<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TransactionNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function useFormat(string $format, string $reset): void
    {
        Setting::setValue('transaction_format', $format);
        Setting::setValue('reset_number', $reset);
    }

    private function storeTransaction(string $number): void
    {
        Transaction::create([
            'transaction_number' => $number,
            'user_id' => User::factory()->create()->id,
            'total' => 1000,
            'paid_amount' => 1000,
        ]);
    }

    public function test_default_format_is_used_when_nothing_is_configured(): void
    {
        Carbon::setTestNow('2026-10-31 10:00:00');

        $this->assertSame('TRX-2026-001', Transaction::generateNumber());
        $this->assertSame('TRX-2026-002', Transaction::generateNumber());
    }

    public function test_custom_format_tokens_are_replaced(): void
    {
        Carbon::setTestNow('2026-03-07 10:00:00');
        $this->useFormat('INV/{YY}{MM}{DD}/{XXXX}', 'daily');

        $this->assertSame('INV/260307/0001', Transaction::generateNumber());
    }

    public function test_daily_reset_starts_again_the_next_day(): void
    {
        $this->useFormat('{YYYY}{MM}{DD}-{XXX}', 'daily');

        Carbon::setTestNow('2026-10-30 21:00:00');
        $this->assertSame('20261030-001', Transaction::generateNumber());
        $this->assertSame('20261030-002', Transaction::generateNumber());

        Carbon::setTestNow('2026-10-31 08:00:00');
        $this->assertSame('20261031-001', Transaction::generateNumber());
    }

    public function test_monthly_reset_starts_again_the_next_month(): void
    {
        $this->useFormat('TRX-{YYYY}{MM}-{XXX}', 'monthly');

        Carbon::setTestNow('2026-10-31 23:00:00');
        $this->assertSame('TRX-202610-001', Transaction::generateNumber());

        Carbon::setTestNow('2026-11-01 07:00:00');
        $this->assertSame('TRX-202611-001', Transaction::generateNumber());
    }

    public function test_never_reset_keeps_counting_across_years(): void
    {
        $this->useFormat('NO-{XXXXX}', 'never');

        Carbon::setTestNow('2026-12-31 22:00:00');
        $this->assertSame('NO-00001', Transaction::generateNumber());

        Carbon::setTestNow('2027-01-01 08:00:00');
        $this->assertSame('NO-00002', Transaction::generateNumber());
    }

    public function test_reset_period_never_outruns_the_dates_in_the_format(): void
    {
        // Monthly reset with only a year in the format would repeat numbers every month.
        $this->useFormat('TRX-{YYYY}-{XXX}', 'monthly');

        Carbon::setTestNow('2026-10-31 10:00:00');
        $this->assertSame('TRX-2026-001', Transaction::generateNumber());

        Carbon::setTestNow('2026-11-01 10:00:00');
        $this->assertSame('TRX-2026-002', Transaction::generateNumber());
    }

    public function test_numbers_already_in_use_are_skipped(): void
    {
        Carbon::setTestNow('2026-10-31 10:00:00');
        $this->storeTransaction('TRX-2026-001');
        $this->storeTransaction('TRX-2026-002');

        $this->assertSame('TRX-2026-003', Transaction::generateNumber());
    }

    public function test_format_without_a_counter_falls_back_to_the_default(): void
    {
        Carbon::setTestNow('2026-10-31 10:00:00');
        $this->useFormat('0', 'yearly');

        $this->assertSame('TRX-2026-001', Transaction::generateNumber());
    }

    public function test_consecutive_checkouts_get_distinct_numbers(): void
    {
        Setting::setValue('tax_enabled', '0');
        $kasir = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        foreach (range(1, 3) as $ignored) {
            $this->actingAs($kasir)->postJson('/kasir/store', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'qris',
                'paid_amount' => 0,
            ])->assertOk();
        }

        $numbers = Transaction::pluck('transaction_number');
        $this->assertCount(3, $numbers->unique());
    }

    public function test_settings_reject_a_format_without_a_counter(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/settings/tax', [
            'transaction_format' => 'TRX-{YYYY}',
            'reset_number' => 'yearly',
        ])->assertSessionHasErrors('transaction_format');
    }

    public function test_settings_reject_a_reset_period_the_format_can_not_show(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/settings/tax', [
            'transaction_format' => 'TRX-{YYYY}-{XXX}',
            'reset_number' => 'daily',
        ])->assertSessionHasErrors('reset_number');
    }

    public function test_settings_accept_a_valid_format(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/settings/tax', [
            'transaction_format' => 'INV/{YYYY}{MM}/{XXXX}',
            'reset_number' => 'monthly',
        ])->assertSessionHasNoErrors();

        $this->assertSame('INV/{YYYY}{MM}/{XXXX}', Setting::getValue('transaction_format'));
    }
}
