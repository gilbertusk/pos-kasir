<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public const DEACTIVATED_MESSAGE = 'Akun Anda telah dinonaktifkan. Hubungi owner toko.';

    /**
     * Log out users whose account was deactivated while they were signed in.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_active) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => self::DEACTIVATED_MESSAGE], 403);
        }

        return redirect()->route('login')->withErrors(['email' => self::DEACTIVATED_MESSAGE]);
    }
}
