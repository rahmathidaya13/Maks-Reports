<?php

namespace App\Http\Controllers\AuthGoogle;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Permission;

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
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Cari user di database berdasarkan google_id
            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect('/login')->with('error', 'Data akun Google tidak valid.');
            }

            // Buat atau ambil user berdasarkan email
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(26)),
                    'email_verified_at' => now(),
                ]
            );


            $user->update(['is_active' => true, 'first_login' => now()]);

            // Jika user lama belum ada google_id, update
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Tandai user
            $user->profile()->firstOrCreate([
                'users_id' => $user->id,
            ]);

            // Cek apakah user ini baru dibuat?, jika ya, berikan role default 'user'.
            if ($user->wasRecentlyCreated) {
                $user->assignRole('user');
            }
            // Cek apakah user belum punya role sama sekali? Jika kosong, kasih 'user'.
            elseif ($user->roles()->count() === 0) {
                $user->assignRole('user');
            }

            Auth::login($user, true);

            if ($user->hasAnyRole(['admin', 'developer'])) {
                return redirect()->intended(route('admin.dashboard.index'));
            }

            return redirect()->intended(route('home'))->with('message', 'Berhasil login menggunakan akun Google.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }
}
