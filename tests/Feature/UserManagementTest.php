<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create();
    }

    public function test_owner_can_delete_a_user_without_history(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($this->owner)
            ->delete("/settings/users/{$kasir->id}")
            ->assertSessionHas('success');

        $this->assertModelMissing($kasir);
    }

    public function test_user_with_transactions_can_not_be_deleted_and_history_survives(): void
    {
        $kasir = User::factory()->create();
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-001',
            'user_id' => $kasir->id,
            'total' => 20000,
            'paid_amount' => 20000,
        ]);

        $this->actingAs($this->owner)
            ->delete("/settings/users/{$kasir->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($kasir);
        $this->assertModelExists($transaction);
    }

    public function test_user_with_expenses_can_not_be_deleted(): void
    {
        $kasir = User::factory()->create();
        Expense::create([
            'user_id' => $kasir->id,
            'date' => '2026-10-24',
            'category' => 'listrik',
            'amount' => 100000,
        ]);

        $this->actingAs($this->owner)
            ->delete("/settings/users/{$kasir->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($kasir);
        $this->assertDatabaseCount('expenses', 1);
    }

    public function test_owner_can_not_delete_their_own_account_from_settings(): void
    {
        $this->actingAs($this->owner)
            ->delete("/settings/users/{$this->owner->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($this->owner);
    }

    public function test_owner_can_not_deactivate_their_own_account(): void
    {
        $this->actingAs($this->owner)
            ->patch("/settings/users/{$this->owner->id}/toggle")
            ->assertSessionHas('error');

        $this->assertTrue($this->owner->fresh()->is_active);
    }

    public function test_owner_can_not_demote_their_own_account(): void
    {
        $this->actingAs($this->owner)
            ->put("/settings/users/{$this->owner->id}", [
                'name' => $this->owner->name,
                'email' => $this->owner->email,
                'role' => 'kasir',
            ])
            ->assertSessionHas('error');

        $this->assertSame('owner', $this->owner->fresh()->role);
    }

    public function test_owner_can_deactivate_another_user(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($this->owner)
            ->patch("/settings/users/{$kasir->id}/toggle")
            ->assertSessionHas('success');

        $this->assertFalse($kasir->fresh()->is_active);
    }

    public function test_user_with_transactions_can_not_delete_their_own_profile(): void
    {
        $kasir = User::factory()->create();
        Transaction::create([
            'transaction_number' => 'TRX-TEST-001',
            'user_id' => $kasir->id,
            'total' => 20000,
            'paid_amount' => 20000,
        ]);

        $this->actingAs($kasir)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertModelExists($kasir);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_last_owner_can_not_delete_their_own_profile(): void
    {
        $this->actingAs($this->owner)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertModelExists($this->owner);
    }
}
