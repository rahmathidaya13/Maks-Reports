<?php

namespace App\Http\Controllers\AuthGoogle;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // Arahkan user ke halaman login Google
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        if (request()->has('error')) {
            return redirect('/login')->with('error', 'Login dengan Google dibatalkan.');
        }

        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cari user di database berdasarkan google_id
            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect('/login')->with('error', 'Data akun Google tidak valid.');
            }

            // Buat atau ambil user berdasarkan email
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(26)),
                    'email_verified_at' => now(),
                    'can_view' => true,
                ]
            );

            // Jika user lama belum ada google_id, update
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Tandai user aktif
            $user->profile()->create();
            $user->assignRole('user');
            $user->update(['is_active' => true, 'first_login' => now()]);

            Auth::login($user, true);

            return redirect()->intended('/home')->with('message', 'Berhasil login menggunakan akun Google.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Google Login Error: ' . $e->getMessage());

            return redirect('/login')->with('error', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }
}
