<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create();
    }

    private function sellOnce(Product $product): Transaction
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-'.$product->id,
            'user_id' => $this->owner->id,
            'subtotal' => $product->sell_price,
            'total' => $product->sell_price,
            'paid_amount' => $product->sell_price,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => $product->sell_price,
            'subtotal' => $product->sell_price,
        ]);

        return $transaction;
    }

    public function test_product_without_sales_history_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->owner)
            ->delete("/products/{$product->id}")
            ->assertRedirect('/products')
            ->assertSessionHas('success');

        $this->assertModelMissing($product);
    }

    public function test_product_with_sales_history_is_kept_and_its_transactions_stay_intact(): void
    {
        $product = Product::factory()->create();
        $transaction = $this->sellOnce($product);

        $this->actingAs($this->owner)
            ->delete("/products/{$product->id}")
            ->assertRedirect('/products')
            ->assertSessionHas('error');

        $this->assertModelExists($product);
        $this->assertSame(1, $transaction->items()->count());
    }

    public function test_database_refuses_to_cascade_delete_sales_history(): void
    {
        $product = Product::factory()->create();
        $this->sellOnce($product);

        $this->expectException(QueryException::class);

        $product->delete();
    }

    public function test_owner_can_toggle_product_status(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->owner)->patch("/products/{$product->id}/toggle")->assertRedirect('/products');

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_product_list_offers_a_toggle_button(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->owner)->get('/products')
            ->assertOk()
            ->assertSee(route('products.toggle', $product), false);
    }

    public function test_validation_errors_are_shown_to_the_user(): void
    {
        Product::factory()->create(['code' => 'MK-001']);

        $this->actingAs($this->owner)
            ->followingRedirects()
            ->from('/products')
            ->post('/products', [
                'code' => 'MK-001',
                'name' => 'Duplikat',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 1000,
                'sell_price' => 2000,
                'stock' => 1,
                'min_stock' => 1,
            ])
            ->assertOk()
            ->assertSee(__('validation.unique', ['attribute' => 'code']));

        $this->assertDatabaseCount('products', 1);
    }
}
