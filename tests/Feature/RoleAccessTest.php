<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function ownerOnlyPages(): array
    {
        return [
            'products' => ['/products'],
            'products pdf' => ['/products/export-pdf'],
            'stock history' => ['/products/stock-history'],
            'reports' => ['/reports'],
            'reports data' => ['/reports/data'],
            'reports export' => ['/reports/export?format=csv&report=harian'],
            'expenses' => ['/expenses'],
            'settings' => ['/settings'],
            'ai insight' => ['/ai/insight'],
        ];
    }

    #[DataProvider('ownerOnlyPages')]
    public function test_kasir_cannot_open_owner_only_pages(string $url): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get($url)->assertForbidden();
    }

    #[DataProvider('ownerOnlyPages')]
    public function test_owner_can_open_owner_only_pages(string $url): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get($url)->assertOk();
    }

    public function test_kasir_can_open_cashier_and_transaction_pages(): void
    {
        $kasir = User::factory()->create();
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-001',
            'user_id' => $kasir->id,
            'total' => 20000,
            'paid_amount' => 20000,
        ]);

        $this->actingAs($kasir)->get('/kasir')->assertOk();
        $this->actingAs($kasir)->get('/kasir/products')->assertOk();
        $this->actingAs($kasir)->get('/transactions')->assertOk();
        $this->actingAs($kasir)->getJson("/transactions/{$transaction->id}")->assertOk();
        $this->actingAs($kasir)->get("/transactions/{$transaction->id}/receipt")->assertOk();
        $this->actingAs($kasir)->get('/profile')->assertOk();
    }

    public function test_kasir_cannot_delete_users(): void
    {
        $owner = User::factory()->owner()->create();
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->delete("/settings/users/{$owner->id}")->assertForbidden();

        $this->assertModelExists($owner);
    }

    public function test_kasir_cannot_modify_products(): void
    {
        $kasir = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($kasir)->delete("/products/{$product->id}")->assertForbidden();
        $this->actingAs($kasir)->patch("/products/{$product->id}/toggle")->assertForbidden();

        $this->assertModelExists($product);
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_kasir_cannot_cancel_transactions(): void
    {
        $kasir = User::factory()->create();
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-001',
            'user_id' => $kasir->id,
            'total' => 20000,
            'paid_amount' => 20000,
        ]);

        $this->actingAs($kasir)->patch("/transactions/{$transaction->id}/cancel")->assertForbidden();

        $this->assertSame('lunas', $transaction->fresh()->status);
    }

    public function test_kasir_does_not_see_owner_menu_items(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/kasir')
            ->assertOk()
            ->assertDontSee(route('settings.index'))
            ->assertDontSee(route('reports.index'));
    }
}
