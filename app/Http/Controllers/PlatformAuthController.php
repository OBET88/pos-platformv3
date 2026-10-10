<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformAuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_LOCKOUT_SECONDS = 900;

    public function create(): View
    {
        return view('platform.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            return $this->sendLockoutResponse($throttleKey);
        }

        if (!Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::LOGIN_LOCKOUT_SECONDS);

            if (RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS) <= 0) {
                return $this->sendLockoutResponse($throttleKey);
            }

            return back()->withErrors([
                'email' => 'Email atau password admin platform tidak valid. '
                    .'Sisa percobaan login: '
                    .RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS)
                    .' dari '
                    .self::MAX_LOGIN_ATTEMPTS
                    .'.',
            ])->onlyInput('email');
        }

        if (!$request->user()?->is_platform_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($throttleKey, self::LOGIN_LOCKOUT_SECONDS);

            if (RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS) <= 0) {
                return $this->sendLockoutResponse($throttleKey);
            }

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki hak akses admin platform. '
                    .'Sisa percobaan login: '
                    .RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS)
                    .' dari '
                    .self::MAX_LOGIN_ATTEMPTS
                    .'.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->route('platform.admin.tenants.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->string('email')->trim()->toString()).'|'.$request->ip();
    }

    private function sendLockoutResponse(string $throttleKey): RedirectResponse
    {
        $seconds = max(RateLimiter::availableIn($throttleKey), 1);
        $minutes = (int) ceil($seconds / 60);
        $retryAt = now()->addSeconds($seconds)->format('H:i');

        return back()->withErrors([
            'email' => 'Terlalu banyak percobaan login yang gagal. '
                .'Akun dikunci sementara selama '
                .$minutes
                .' menit. Silakan coba lagi setelah pukul '
                .$retryAt
                .'.',
        ])->onlyInput('email');
    }
}
