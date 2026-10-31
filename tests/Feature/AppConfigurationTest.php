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
        foreach (['unzip.php', 'storage-link.php', 'clear-cache.php', 'migrate.php'] as $script) {
            $this->assertFileDoesNotExist(public_path($script));
        }
    }

    public function test_deployment_scripts_that_change_the_server_require_a_secret(): void
    {
        foreach (['clear-cache.php', 'migrate.php'] as $script) {
            $source = file_get_contents(base_path("deploy/{$script}"));

            $this->assertStringContainsString("\$secret = '';", $source, "{$script} must ship with an empty secret");
            $this->assertStringContainsString('hash_equals', $source, "{$script} must compare the secret safely");
        }
    }

    public function test_application_code_reads_configuration_instead_of_env(): void
    {
        // env() returns null once `php artisan config:cache` has run, which production deploys do.
        $offenders = collect(\Illuminate\Support\Facades\File::allFiles(app_path()))
            ->merge(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
            ->filter(fn ($file) => preg_match('/\benv\(/', $file->getContents()))
            ->map(fn ($file) => $file->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_gemini_settings_come_from_config(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Stok aman.']]]]]]),
        ]);
        \App\Models\Product::factory()->create();
        $owner = User::factory()->owner()->create();

        config(['services.gemini.key' => null]);
        $this->actingAs($owner)->postJson('/ai/chat', ['message' => 'Halo'])->assertStatus(400);

        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        $this->actingAs($owner)->postJson('/ai/chat', ['message' => 'Halo'])
            ->assertOk()
            ->assertJsonPath('reply', 'Stok aman.');

        \Illuminate\Support\Facades\Http::assertSent(fn ($request) => str_contains($request->url(), 'models/gemini-test:generateContent')
            && $request->header('x-goog-api-key')[0] === 'test-key');
    }
}
