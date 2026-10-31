<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::factory()->create(['name' => 'Siti']);
        $product = Product::factory()->create(['name' => 'Ayam Geprek', 'sell_price' => 20000]);

        $this->transaction = Transaction::create([
            'transaction_number' => 'TRX-2026-001',
            'user_id' => $this->kasir->id,
            'subtotal' => 20000,
            'total' => 20000,
            'paid_amount' => 50000,
            'change_amount' => 30000,
        ]);

        TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 20000,
            'buy_price' => 10000,
            'subtotal' => 20000,
        ]);
    }

    private function receipt(string $query = '')
    {
        return $this->actingAs($this->kasir)->get("/transactions/{$this->transaction->id}/receipt{$query}");
    }

    public function test_receipt_shows_store_profile_from_settings(): void
    {
        Setting::setValue('store_name', 'Warung Bu Tini');
        Setting::setValue('store_address', 'Jl. Kenanga 5');
        Setting::setValue('store_phone', '021-555');
        Setting::setValue('store_whatsapp', '0812-3456');
        Setting::setValue('store_npwp', '12.345.678.9-012.000');

        $this->receipt()
            ->assertOk()
            ->assertSee('Warung Bu Tini')
            ->assertSee('Jl. Kenanga 5')
            ->assertSee('021-555')
            ->assertSee('WA 0812-3456')
            ->assertSee('NPWP 12.345.678.9-012.000')
            ->assertSee('TRX-2026-001')
            ->assertSee('Ayam Geprek')
            ->assertSee('Siti');
    }

    public function test_empty_whatsapp_and_npwp_are_not_printed(): void
    {
        Setting::setValue('store_whatsapp', '');
        Setting::setValue('store_npwp', null);

        $this->receipt()->assertOk()->assertDontSee('WA ')->assertDontSee('NPWP');
    }

    public function test_paper_size_and_margin_set_the_printable_width(): void
    {
        Setting::setValue('printer_size', '58mm');
        Setting::setValue('printer_margin', '3');
        $this->receipt()->assertOk()->assertSee('width: 52mm', false)->assertSee('padding: 0 3mm', false);

        Setting::setValue('printer_size', '80mm');
        Setting::setValue('printer_margin', '5');
        $this->receipt()->assertOk()->assertSee('width: 70mm', false);
    }

    public function test_auto_print_setting_controls_printing_when_opened_directly(): void
    {
        Setting::setValue('auto_print', '1');
        $this->receipt()->assertOk()->assertSee('window.print()', false);

        Setting::setValue('auto_print', '0');
        $this->receipt()->assertOk()->assertDontSee('window.print()', false);
    }

    public function test_print_query_overrides_the_auto_print_setting(): void
    {
        Setting::setValue('auto_print', '0');
        $this->receipt('?print=1')->assertOk()->assertSee('window.print()', false);

        Setting::setValue('auto_print', '1');
        $this->receipt('?print=0')->assertOk()->assertDontSee('window.print()', false);
    }

    public function test_rounding_is_shown_on_the_receipt_only_when_applied(): void
    {
        $this->receipt()->assertOk()->assertDontSee('Pembulatan');

        $this->transaction->update(['rounding_amount' => -200, 'total' => 19800]);
        $this->receipt()->assertOk()->assertSee('Pembulatan')->assertSee('-Rp 200');
    }

    public function test_cancelled_sale_receipt_is_clearly_marked(): void
    {
        $this->transaction->cancel(User::factory()->owner()->create(), 'Salah input');

        $this->receipt()->assertOk()->assertSee('DIBATALKAN');
    }

    public function test_cashier_screen_previews_the_same_receipt_page(): void
    {
        Setting::setValue('auto_print', '0');

        $this->actingAs($this->kasir)->get('/kasir')
            ->assertOk()
            ->assertViewHas('autoPrint', false)
            ->assertSee('x-ref="receiptFrame"', false)
            ->assertDontSee('receipt-content', false);
    }

    public function test_reprint_links_force_printing(): void
    {
        $this->actingAs($this->kasir)->get('/transactions')
            ->assertOk()
            ->assertSee("/transactions/{$this->transaction->id}/receipt?print=1", false);
    }

    public function test_settings_reject_a_margin_wider_than_the_paper_allows(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/settings/tax', [
            'transaction_format' => 'TRX-{YYYY}-{XXX}',
            'reset_number' => 'yearly',
            'printer_size' => '58mm',
            'printer_margin' => 30,
        ])->assertSessionHasErrors('printer_margin');
    }
}
