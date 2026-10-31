<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitReportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create();
        Setting::setValue('tax_enabled', '0');
        Setting::setValue('max_discount', '50');
    }

    private function sell(Product $product, int $quantity, string $status = 'lunas'): Transaction
    {
        static $sequence = 0;
        $sequence++;

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-' . $sequence,
            'user_id' => $this->owner->id,
            'subtotal' => $product->sell_price * $quantity,
            'total' => $product->sell_price * $quantity,
            'paid_amount' => $product->sell_price * $quantity,
            'status' => $status,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'price' => $product->sell_price,
            'buy_price' => $product->buy_price,
            'subtotal' => $product->sell_price * $quantity,
        ]);

        return $transaction;
    }

    private function reportSummary(): array
    {
        $today = now()->toDateString();

        return $this->actingAs($this->owner)
            ->getJson("/reports/data?start_date={$today}&end_date={$today}")
            ->assertOk()
            ->json('summary');
    }

    public function test_checkout_stores_the_buy_price_at_the_time_of_sale(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);

        $this->actingAs($this->owner)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();

        $product->update(['buy_price' => 9000]);

        $this->assertEquals(6000, TransactionItem::sole()->buy_price);
    }

    public function test_gross_profit_subtracts_cost_of_goods_sold(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);
        $this->sell($product, 1);

        $summary = $this->reportSummary();

        $this->assertEquals(10000, $summary['total_revenue']);
        $this->assertEquals(6000, $summary['total_cogs']);
        $this->assertEquals(4000, $summary['gross_profit']);
    }

    public function test_net_profit_also_subtracts_expenses(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);
        $this->sell($product, 3);

        Expense::create([
            'user_id' => $this->owner->id,
            'date' => now()->toDateString(),
            'category' => 'listrik',
            'amount' => 5000,
        ]);

        $summary = $this->reportSummary();

        $this->assertEquals(12000, $summary['gross_profit']);
        $this->assertEquals(5000, $summary['total_expenses']);
        $this->assertEquals(7000, $summary['net_profit']);
    }

    public function test_cancelled_sales_are_not_counted(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);
        $this->sell($product, 1);
        $this->sell($product, 5, 'batal');

        $summary = $this->reportSummary();

        $this->assertEquals(1, $summary['total_transactions']);
        $this->assertEquals(10000, $summary['total_revenue']);
        $this->assertEquals(6000, $summary['total_cogs']);
    }

    public function test_discount_reduces_profit_but_tax_does_not_count_as_revenue(): void
    {
        Setting::setValue('tax_enabled', '1');
        Setting::setValue('tax_percent', '10');
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);

        $this->actingAs($this->owner)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount_percent' => 10,
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();

        $summary = $this->reportSummary();

        // 20.000 - diskon 2.000 = 18.000 (PPN 1.800 milik negara, bukan pendapatan)
        $this->assertEquals(18000, $summary['total_revenue']);
        $this->assertEquals(12000, $summary['total_cogs']);
        $this->assertEquals(6000, $summary['gross_profit']);
    }

    public function test_transaction_page_summary_follows_the_date_filter(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);

        $this->travelTo(now()->subDays(10));
        $this->sell($product, 5);
        $this->travelBack();

        $this->sell($product, 1);
        $today = now()->toDateString();

        $this->actingAs($this->owner)->get("/transactions?start_date={$today}&end_date={$today}")
            ->assertOk()
            ->assertViewHas('totalTransactions', 1)
            ->assertViewHas('totalRevenue', fn ($value) => (float) $value === 10000.0)
            ->assertViewHas('grossProfit', fn ($value) => (float) $value === 4000.0);
    }

    public function test_transaction_page_summary_covers_everything_without_a_filter(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);

        $this->travelTo(now()->subDays(10));
        $this->sell($product, 5);
        $this->travelBack();

        $this->sell($product, 1);

        $this->actingAs($this->owner)->get('/transactions')
            ->assertOk()
            ->assertViewHas('totalTransactions', 2)
            ->assertViewHas('totalRevenue', fn ($value) => (float) $value === 60000.0);
    }

    public function test_transaction_page_says_which_period_the_summary_covers(): void
    {
        $this->actingAs($this->owner)->get('/transactions?start_date=2026-10-01&end_date=2026-10-31')
            ->assertOk()
            ->assertSee('01/10/2026 – 31/10/2026');

        $this->actingAs($this->owner)->get('/transactions')
            ->assertOk()
            ->assertSee('Semua tanggal');
    }

    public function test_transaction_page_profit_uses_cost_of_goods_sold(): void
    {
        $product = Product::factory()->create(['buy_price' => 6000, 'sell_price' => 10000]);
        $this->sell($product, 1);

        $this->actingAs($this->owner)->get('/transactions')
            ->assertOk()
            ->assertViewHas('grossProfit', fn ($value) => (float) $value === 4000.0);
    }
}
