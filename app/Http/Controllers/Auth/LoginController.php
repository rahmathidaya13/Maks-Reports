<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Lockout;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|max:50',
            // 'g-recaptcha-response' => 'required',
        ], [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar di sistem.',

            'password.required' => 'Password tidak boleh kosong',
            'password.string' => 'Password harus berupa karakter yang valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 50 karakter.',

            // 'g-recaptcha-response.required' => 'Harap centang kotak reCAPTCHA terlebih dahulu.',
        ]);
        // if ( !RecaptchaService::verify( $request->input( 'g-recaptcha-response' ) ) ) {
        //     return back()->withErrors( [ 'recaptcha' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.' ] );
        // }

        $this->authenticate($request);
        if (Auth::user()->status === 'inactive') {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Akses ditolak.',
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended('/dashboard/analitics');
    }

    public function authenticate(Request $request): void
    {
        $this->ensureIsNotRateLimited($request);
        if (!Auth::attempt($request->only(['email', 'password']), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'error' => ['Email atau password salah.'],
            ]);
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user->is_active) {
            $user->status = 'active';
            $user->first_login = now();
            $user->is_active = true;
            $user->save();
        }
        RateLimiter::clear($this->throttleKey($request));
    }

    public function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 3)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'email' => [
                'Kredensial ini tidak sesuai dengan data yang ada',
                "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ],
        ]);
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')) . '|' . $request->ip());
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = User::where('email', Auth::user()->email)->first();
        if ($user && $user->is_active) {
            $user->is_active = false;
            $user->last_login = now();
            $user->setRememberToken(null);
            $user->save();
        }
        Auth::guard('web')->logout();

        // Hapus sesi & regenerasi token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
