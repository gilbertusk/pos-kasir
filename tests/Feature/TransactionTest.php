<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function makeSale(User $user, Product $product, int $quantity): Transaction
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-001',
            'user_id' => $user->id,
            'subtotal' => $product->sell_price * $quantity,
            'total' => $product->sell_price * $quantity,
            'paid_amount' => $product->sell_price * $quantity,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'price' => $product->sell_price,
            'subtotal' => $product->sell_price * $quantity,
        ]);

        return $transaction;
    }

    public function test_detail_endpoint_returns_items_and_cashier(): void
    {
        $kasir = User::factory()->create(['name' => 'Siti']);
        $product = Product::factory()->create(['name' => 'Ayam Geprek', 'sell_price' => 20000]);
        $transaction = $this->makeSale($kasir, $product, 2);

        $this->actingAs($kasir)
            ->getJson("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('transaction_number', 'TRX-TEST-001')
            ->assertJsonPath('user.name', 'Siti')
            ->assertJsonPath('items.0.product_name', 'Ayam Geprek')
            ->assertJsonPath('items.0.quantity', 2);
    }

    public function test_transaction_page_detail_modal_uses_an_alpine_component(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/transactions')
            ->assertOk()
            ->assertSee('transactionsPage(', false)
            ->assertDontSee('__x.$data', false);
    }

    public function test_kasir_does_not_see_store_profit_summary(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/transactions')
            ->assertOk()
            ->assertDontSee('Laba Kotor');
    }

    public function test_owner_cancelling_a_sale_restores_stock_once(): void
    {
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $transaction = $this->makeSale($owner, $product, 3);

        $this->actingAs($owner)->patch("/transactions/{$transaction->id}/cancel")->assertSessionHas('success');
        $this->actingAs($owner)->patch("/transactions/{$transaction->id}/cancel")->assertSessionHas('error');

        $this->assertSame('batal', $transaction->fresh()->status);
        $this->assertSame(13, $product->fresh()->stock);
    }
}
