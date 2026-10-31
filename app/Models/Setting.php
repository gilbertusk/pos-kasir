<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /** All settings are cached together; the table is small and read on almost every page. */
    public const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        // Model events cover setValue(), create() in the seeder and deletes.
        // Mass updates (Setting::where(...)->update()) skip these events and must call forgetCache().
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The stored value, or $default when the key was never saved. A saved empty value is returned as is.
     */
    public static function getValue(string $key, $default = null)
    {
        $settings = static::allCached();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getMultiple(array $keys): array
    {
        $settings = static::allCached();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }
        return $result;
    }
}
