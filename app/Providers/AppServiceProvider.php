<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Counted per user, not per IP: every cashier in a shop usually shares one connection.
        // AI calls cost Gemini quota; checkout is capped well above what a real cashier can ring up.
        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)
            ->by('ai:' . ($request->user()?->id ?: $request->ip()))
            ->response(fn () => $this->tooManyRequests('Terlalu banyak permintaan ke AI Assistant. Tunggu sebentar lalu coba lagi.')));

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(60)
            ->by('checkout:' . ($request->user()?->id ?: $request->ip()))
            ->response(fn () => $this->tooManyRequests('Terlalu banyak transaksi dalam satu menit. Tunggu sebentar lalu coba lagi.')));
    }

    private function tooManyRequests(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 429);
    }
}
