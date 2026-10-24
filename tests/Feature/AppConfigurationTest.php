<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_uses_indonesian_western_time_by_default(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_expense_date_serializes_without_a_timezone_shift(): void
    {
        $expense = Expense::create([
            'user_id' => User::factory()->create()->id,
            'date' => '2026-10-24',
            'category' => 'listrik',
            'amount' => 100000,
        ]);

        $this->assertSame('2026-10-24', $expense->fresh()->toArray()['date']);
    }

    public function test_deployment_helper_scripts_are_not_publicly_reachable(): void
    {
        $this->assertFileDoesNotExist(public_path('unzip.php'));
        $this->assertFileDoesNotExist(public_path('storage-link.php'));
    }
}
