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
            ->assertDontSee('Laba kotor');
    }

    public function test_owner_sees_store_profit_summary(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/transactions')
            ->assertOk()
            ->assertSee('Laba kotor');
    }

    public function test_owner_cancelling_a_sale_restores_stock_once(): void
    {
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $transaction = $this->makeSale($owner, $product, 3);

        $this->actingAs($owner)->patch("/transactions/{$transaction->id}/cancel", ['cancel_reason' => 'Salah input'])->assertSessionHas('success');
        $this->actingAs($owner)->patch("/transactions/{$transaction->id}/cancel", ['cancel_reason' => 'Salah input'])->assertSessionHas('error');

        $this->assertSame('batal', $transaction->fresh()->status);
        $this->assertSame(13, $product->fresh()->stock);
    }

    public function test_cancelling_records_who_when_and_why(): void
    {
        $owner = User::factory()->owner()->create();
        $transaction = $this->makeSale($owner, Product::factory()->create(), 1);

        $this->actingAs($owner)->patch("/transactions/{$transaction->id}/cancel", [
            'cancel_reason' => 'Pelanggan batal pesan',
        ])->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame($owner->id, $transaction->cancelled_by);
        $this->assertNotNull($transaction->cancelled_at);
        $this->assertSame('Pelanggan batal pesan', $transaction->cancel_reason);
    }

    public function test_cancelling_requires_a_reason(): void
    {
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $transaction = $this->makeSale($owner, $product, 3);

        $this->actingAs($owner)
            ->patch("/transactions/{$transaction->id}/cancel", ['cancel_reason' => '  '])
            ->assertSessionHasErrors('cancel_reason');

        $this->assertSame('lunas', $transaction->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_cancelling_an_already_cancelled_sale_does_not_restore_stock_again(): void
    {
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $transaction = $this->makeSale($owner, $product, 3);

        // Simulates a second request that loaded the sale before the first one committed.
        $stale = Transaction::find($transaction->id);
        $transaction->cancel($owner, 'Pertama');

        $this->assertFalse($stale->cancel($owner, 'Kedua'));
        $this->assertSame(13, $product->fresh()->stock);
        $this->assertSame('Pertama', $transaction->fresh()->cancel_reason);
    }

    public function test_detail_endpoint_includes_who_cancelled(): void
    {
        $owner = User::factory()->owner()->create(['name' => 'Akbar']);
        $transaction = $this->makeSale($owner, Product::factory()->create(), 1);
        $transaction->cancel($owner, 'Salah input');

        $this->actingAs($owner)
            ->getJson("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('cancel_reason', 'Salah input')
            ->assertJsonPath('canceller.name', 'Akbar');
    }

    public function test_owner_sees_cancel_button_but_kasir_does_not(): void
    {
        $owner = User::factory()->owner()->create();
        $kasir = User::factory()->create();

        $this->actingAs($owner)->get('/transactions')->assertOk()->assertSee('Batalkan transaksi');
        $this->actingAs($kasir)->get('/transactions')->assertOk()->assertDontSee('Batalkan transaksi');
    }

    public function test_user_who_cancelled_a_sale_has_activity_history(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $transaction = $this->makeSale($owner, Product::factory()->create(), 1);
        $transaction->cancel($otherOwner, 'Salah input');

        $this->assertTrue($otherOwner->hasActivityHistory());
    }
}
