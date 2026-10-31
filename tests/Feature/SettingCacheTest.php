<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    use RefreshDatabase;

    private function countSettingQueries(callable $callback): int
    {
        $count = 0;
        DB::listen(function ($query) use (&$count) {
            if (str_contains($query->sql, '"settings"') || str_contains($query->sql, '`settings`')) {
                $count++;
            }
        });

        $callback();

        return $count;
    }

    public function test_settings_are_read_from_the_database_only_once(): void
    {
        Setting::setValue('store_name', 'Warung Bu Tini');
        Setting::setValue('tax_percent', '11');

        $queries = $this->countSettingQueries(function () {
            Setting::getValue('store_name');
            Setting::getValue('tax_percent');
            Setting::getMultiple(['store_name', 'tax_percent', 'auto_print']);
            Setting::getValue('store_name');
        });

        $this->assertSame(1, $queries);
    }

    public function test_changing_a_setting_is_visible_immediately(): void
    {
        Setting::setValue('tax_percent', '11');
        $this->assertSame('11', Setting::getValue('tax_percent'));

        Setting::setValue('tax_percent', '12');
        $this->assertSame('12', Setting::getValue('tax_percent'));
    }

    public function test_settings_created_directly_also_clear_the_cache(): void
    {
        $this->assertNull(Setting::getValue('store_name'));

        // The seeder writes settings with create(), bypassing setValue().
        Setting::create(['key' => 'store_name', 'value' => 'Warung Baru']);
        $this->assertSame('Warung Baru', Setting::getValue('store_name'));

        Setting::where('key', 'store_name')->first()->delete();
        $this->assertNull(Setting::getValue('store_name'));
    }

    public function test_missing_setting_returns_the_default_but_a_stored_empty_value_does_not(): void
    {
        $this->assertSame('fallback', Setting::getValue('store_npwp', 'fallback'));

        Setting::setValue('store_npwp', null);
        $this->assertNull(Setting::getValue('store_npwp', 'fallback'));

        $this->assertSame(['store_npwp' => null, 'store_phone' => null], Setting::getMultiple(['store_npwp', 'store_phone']));
    }

    public function test_saving_settings_page_updates_what_the_cashier_sees(): void
    {
        $owner = User::factory()->owner()->create();
        Setting::setValue('max_discount', '50');
        $this->actingAs($owner)->get('/kasir')->assertViewHas('maxDiscount', '50');

        $this->actingAs($owner)->post('/settings/tax', [
            'max_discount' => 20,
            'transaction_format' => 'TRX-{YYYY}-{XXX}',
            'reset_number' => 'yearly',
        ])->assertSessionHasNoErrors();

        $this->actingAs($owner)->get('/kasir')->assertViewHas('maxDiscount', '20');
    }
}
