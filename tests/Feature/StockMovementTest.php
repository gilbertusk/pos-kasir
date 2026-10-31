<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create(['name' => 'Bu Tini']);
    }

    private function restock(Product $product, array $data)
    {
        return $this->actingAs($this->owner)->from('/products')->post("/products/{$product->id}/stock", $data);
    }

    public function test_owner_can_record_incoming_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->restock($product, ['type' => 'masuk', 'quantity' => 10, 'note' => 'Belanja pasar'])
            ->assertRedirect('/products')
            ->assertSessionHas('success');

        $this->assertSame(15, $product->fresh()->stock);

        $movement = StockMovement::sole();
        $this->assertSame('masuk', $movement->type);
        $this->assertSame(10, $movement->quantity);
        $this->assertSame(5, $movement->stock_before);
        $this->assertSame(15, $movement->stock_after);
        $this->assertSame($this->owner->id, $movement->user_id);
        $this->assertSame('Belanja pasar', $movement->note);
    }

    public function test_incoming_stock_must_be_at_least_one(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->restock($product, ['type' => 'masuk', 'quantity' => 0])->assertSessionHasErrors('quantity');
        $this->restock($product, ['type' => 'masuk', 'quantity' => -3])->assertSessionHasErrors('quantity');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_adjustment_sets_the_counted_stock_and_requires_a_reason(): void
    {
        $product = Product::factory()->create(['stock' => 20]);

        $this->restock($product, ['type' => 'penyesuaian', 'actual_stock' => 17, 'note' => ' '])
            ->assertSessionHasErrors('note');
        $this->assertSame(20, $product->fresh()->stock);

        $this->restock($product, ['type' => 'penyesuaian', 'actual_stock' => 17, 'note' => '3 bungkus rusak'])
            ->assertSessionHas('success');

        $this->assertSame(17, $product->fresh()->stock);
        $movement = StockMovement::sole();
        $this->assertSame('penyesuaian', $movement->type);
        $this->assertSame(-3, $movement->quantity);
        $this->assertSame(20, $movement->stock_before);
        $this->assertSame(17, $movement->stock_after);
    }

    public function test_adjustment_can_not_go_negative_or_leave_stock_unchanged(): void
    {
        $product = Product::factory()->create(['stock' => 8]);

        $this->restock($product, ['type' => 'penyesuaian', 'actual_stock' => -1, 'note' => 'Hilang'])
            ->assertSessionHasErrors('actual_stock');
        $this->restock($product, ['type' => 'penyesuaian', 'actual_stock' => 8, 'note' => 'Cek'])
            ->assertSessionHasErrors('actual_stock');

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_unknown_movement_type_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 8]);

        $this->restock($product, ['type' => 'penjualan', 'quantity' => 5])->assertSessionHasErrors('type');
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_kasir_can_not_change_stock_or_see_the_history(): void
    {
        $kasir = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($kasir)->post("/products/{$product->id}/stock", ['type' => 'masuk', 'quantity' => 5])->assertForbidden();
        $this->actingAs($kasir)->get('/products/stock-history')->assertForbidden();

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_editing_a_product_does_not_change_its_stock(): void
    {
        $product = Product::factory()->create(['stock' => 12, 'code' => 'MK-001']);

        $this->actingAs($this->owner)->put("/products/{$product->id}", [
            'code' => 'MK-001',
            'name' => 'Nama Baru',
            'category' => 'makanan',
            'unit' => 'Porsi',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'stock' => 999,
            'min_stock' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $product->fresh()->name);
        $this->assertSame(12, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_new_product_records_its_opening_stock(): void
    {
        $this->actingAs($this->owner)->post('/products', [
            'code' => 'MN-009',
            'name' => 'Es Jeruk',
            'category' => 'minuman',
            'unit' => 'Gelas',
            'buy_price' => 3000,
            'sell_price' => 7000,
            'stock' => 25,
            'min_stock' => 5,
        ])->assertSessionHasNoErrors();

        $movement = StockMovement::sole();
        $this->assertSame('awal', $movement->type);
        $this->assertSame(25, $movement->quantity);
        $this->assertSame(0, $movement->stock_before);
        $this->assertSame(25, $movement->stock_after);
        $this->assertSame($this->owner->id, $movement->user_id);
    }

    public function test_new_product_without_stock_has_no_history(): void
    {
        $this->actingAs($this->owner)->post('/products', [
            'code' => 'MN-010',
            'name' => 'Es Teh',
            'category' => 'minuman',
            'unit' => 'Gelas',
            'buy_price' => 1000,
            'sell_price' => 4000,
            'stock' => 0,
            'min_stock' => 5,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_sales_and_cancellations_are_recorded_with_the_transaction(): void
    {
        $kasir = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($kasir)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();

        $transaction = Transaction::sole();
        $sale = StockMovement::sole();
        $this->assertSame('penjualan', $sale->type);
        $this->assertSame(-3, $sale->quantity);
        $this->assertSame(10, $sale->stock_before);
        $this->assertSame(7, $sale->stock_after);
        $this->assertSame($kasir->id, $sale->user_id);
        $this->assertSame($transaction->id, $sale->transaction_id);

        $transaction->cancel($this->owner, 'Salah input');

        $return = StockMovement::where('type', 'batal')->sole();
        $this->assertSame(3, $return->quantity);
        $this->assertSame(7, $return->stock_before);
        $this->assertSame(10, $return->stock_after);
        $this->assertSame($this->owner->id, $return->user_id);
        $this->assertSame($transaction->id, $return->transaction_id);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_history_adds_up_to_the_current_stock(): void
    {
        $kasir = User::factory()->create();
        $this->actingAs($this->owner)->post('/products', [
            'code' => 'MK-020', 'name' => 'Ayam Bakar', 'category' => 'makanan', 'unit' => 'Porsi',
            'buy_price' => 10000, 'sell_price' => 20000, 'stock' => 10, 'min_stock' => 2,
        ]);
        $product = Product::sole();

        $this->restock($product, ['type' => 'masuk', 'quantity' => 5]);
        $this->actingAs($kasir)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();
        $this->restock($product, ['type' => 'penyesuaian', 'actual_stock' => 9, 'note' => 'Opname']);

        $this->assertSame(9, $product->fresh()->stock);
        $this->assertSame(9, (int) StockMovement::where('product_id', $product->id)->sum('quantity'));
        $this->assertSame(9, StockMovement::where('product_id', $product->id)->latest('id')->first()->stock_after);
    }

    public function test_history_page_lists_movements_and_filters_by_product(): void
    {
        $nasi = Product::factory()->create(['name' => 'Nasi Uduk', 'stock' => 5]);
        $teh = Product::factory()->create(['name' => 'Teh Tawar', 'stock' => 5]);
        $this->restock($nasi, ['type' => 'masuk', 'quantity' => 4, 'note' => 'Supplier Pak Budi']);
        $this->restock($teh, ['type' => 'penyesuaian', 'actual_stock' => 2, 'note' => 'Tumpah']);

        $this->actingAs($this->owner)->get('/products/stock-history')
            ->assertOk()
            ->assertSee('Nasi Uduk')
            ->assertSee('Teh Tawar')
            ->assertSee('Supplier Pak Budi')
            ->assertSee('Bu Tini');

        $this->actingAs($this->owner)->get("/products/stock-history?product_id={$nasi->id}")
            ->assertOk()
            ->assertSee('Supplier Pak Budi')
            ->assertDontSee('Tumpah');

        $this->actingAs($this->owner)->get('/products/stock-history?type=penyesuaian')
            ->assertOk()
            ->assertSee('Tumpah')
            ->assertDontSee('Supplier Pak Budi');
    }

    public function test_history_page_links_sales_to_their_transaction(): void
    {
        $kasir = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $this->actingAs($kasir)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();

        $this->actingAs($this->owner)->get('/products/stock-history')
            ->assertOk()
            ->assertSee(Transaction::sole()->transaction_number);
    }

    public function test_product_with_only_stock_history_can_still_be_deleted(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->restock($product, ['type' => 'masuk', 'quantity' => 5]);

        $this->actingAs($this->owner)->delete("/products/{$product->id}")->assertSessionHas('success');

        $this->assertModelMissing($product);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_demo_data_history_matches_product_stock(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        foreach (Product::all() as $product) {
            $this->assertSame(
                $product->stock,
                (int) $product->stockMovements()->sum('quantity'),
                "Riwayat stok {$product->name} tidak cocok dengan stoknya"
            );
        }
    }

    public function test_user_who_changed_stock_can_not_be_deleted(): void
    {
        $staff = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $this->actingAs($staff)->post("/products/{$product->id}/stock", ['type' => 'masuk', 'quantity' => 1]);

        $this->assertTrue($staff->hasActivityHistory());

        $this->actingAs($this->owner)->delete("/settings/users/{$staff->id}")->assertSessionHas('error');
        $this->assertModelExists($staff);
    }
}
