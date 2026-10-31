<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function checkout(User $kasir, Product $product)
    {
        return $this->actingAs($kasir)->postJson('/kasir/store', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ]);
    }

    public function test_ai_chat_is_limited_to_ten_requests_per_minute(): void
    {
        Http::fake();
        $owner = User::factory()->owner()->create();

        foreach (range(1, 10) as $ignored) {
            $status = $this->actingAs($owner)->postJson('/ai/chat', ['message' => 'Stok apa yang habis?'])->status();
            $this->assertNotSame(429, $status);
        }

        $this->actingAs($owner)->postJson('/ai/chat', ['message' => 'Stok apa yang habis?'])
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Terlalu banyak'));
    }

    public function test_ai_insight_shares_the_ai_limit(): void
    {
        Http::fake();
        $owner = User::factory()->owner()->create();

        foreach (range(1, 10) as $ignored) {
            $this->actingAs($owner)->getJson('/ai/insight')->assertOk();
        }

        $this->actingAs($owner)->getJson('/ai/insight')->assertStatus(429);
        $this->actingAs($owner)->postJson('/ai/chat', ['message' => 'Halo'])->assertStatus(429);
    }

    public function test_ai_limit_is_counted_per_user(): void
    {
        $first = User::factory()->owner()->create();
        $second = User::factory()->owner()->create();

        foreach (range(1, 10) as $ignored) {
            $this->actingAs($first)->getJson('/ai/insight')->assertOk();
        }

        $this->actingAs($first)->getJson('/ai/insight')->assertStatus(429);
        $this->actingAs($second)->getJson('/ai/insight')->assertOk();
    }

    public function test_checkout_is_limited_to_sixty_requests_per_minute(): void
    {
        Setting::setValue('tax_enabled', '0');
        $kasir = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);

        foreach (range(1, 60) as $ignored) {
            $this->checkout($kasir, $product)->assertOk();
        }

        $this->checkout($kasir, $product)
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Terlalu banyak'));

        $this->assertSame(40, $product->fresh()->stock);
    }

    public function test_limit_resets_after_a_minute(): void
    {
        $owner = User::factory()->owner()->create();

        foreach (range(1, 10) as $ignored) {
            $this->actingAs($owner)->getJson('/ai/insight');
        }
        $this->actingAs($owner)->getJson('/ai/insight')->assertStatus(429);

        $this->travel(61)->seconds();

        $this->actingAs($owner)->getJson('/ai/insight')->assertOk();
    }
}
