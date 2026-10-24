<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::factory()->create();
        Setting::setValue('tax_enabled', '1');
        Setting::setValue('tax_percent', '11');
        Setting::setValue('max_discount', '50');
    }

    private function checkout(array $payload)
    {
        return $this->actingAs($this->kasir)->postJson('/kasir/store', $payload);
    }

    public function test_cash_sale_is_saved_with_server_side_totals_and_decrements_stock(): void
    {
        $ayam = Product::factory()->create(['sell_price' => 20000, 'stock' => 30]);
        $teh = Product::factory()->create(['sell_price' => 5000, 'stock' => 100]);

        $response = $this->checkout([
            'items' => [
                ['product_id' => $ayam->id, 'quantity' => 2],
                ['product_id' => $teh->id, 'quantity' => 1],
            ],
            'discount_percent' => 10,
            'payment_method' => 'tunai',
            'paid_amount' => 100000,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $transaction = Transaction::sole();
        $this->assertEquals(45000, $transaction->subtotal);
        $this->assertEquals(4500, $transaction->discount_amount);
        $this->assertEquals(4455, $transaction->tax_amount);
        $this->assertEquals(44955, $transaction->total);
        $this->assertEquals(55045, $transaction->change_amount);
        $this->assertSame('lunas', $transaction->status);
        $this->assertSame(28, $ayam->fresh()->stock);
        $this->assertSame(99, $teh->fresh()->stock);
    }

    public function test_cash_sale_is_rejected_when_paid_amount_is_less_than_total(): void
    {
        $product = Product::factory()->create(['sell_price' => 18000, 'stock' => 10]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount_percent' => 0,
            'payment_method' => 'tunai',
            'paid_amount' => 1000,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_discount_above_the_configured_maximum_is_rejected(): void
    {
        $product = Product::factory()->create(['sell_price' => 18000]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount_percent' => 90,
            'payment_method' => 'tunai',
            'paid_amount' => 50000,
        ])->assertUnprocessable()->assertJsonValidationErrors('discount_percent');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_tax_comes_from_settings_not_from_the_client(): void
    {
        $product = Product::factory()->create(['sell_price' => 18000]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'tax_percent' => 0,
            'payment_method' => 'qris',
            'paid_amount' => 18000,
        ])->assertOk();

        $transaction = Transaction::sole();
        $this->assertEquals(11, $transaction->tax_percent);
        $this->assertEquals(1980, $transaction->tax_amount);
        $this->assertEquals(19980, $transaction->total);
    }

    public function test_no_tax_is_charged_when_tax_is_disabled(): void
    {
        Setting::setValue('tax_enabled', '0');
        $product = Product::factory()->create(['sell_price' => 18000]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'tax_percent' => 11,
            'payment_method' => 'tunai',
            'paid_amount' => 18000,
        ])->assertOk();

        $this->assertEquals(0, Transaction::sole()->tax_amount);
        $this->assertEquals(18000, Transaction::sole()->total);
    }

    public function test_non_cash_payment_is_recorded_as_exactly_the_total(): void
    {
        $product = Product::factory()->create(['sell_price' => 18000]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'debit',
            'paid_amount' => 1,
        ])->assertOk();

        $transaction = Transaction::sole();
        $this->assertEquals(19980, $transaction->paid_amount);
        $this->assertEquals(0, $transaction->change_amount);
    }

    public function test_sale_is_rejected_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 999]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_duplicate_lines_can_not_bypass_the_stock_check(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->checkout([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertUnprocessable();

        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_inactive_products_can_not_be_sold(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 0);
    }
}
