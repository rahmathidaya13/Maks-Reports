<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showConfirmForm()
    {
        return Inertia::render('Auth/Passwords/Confirm');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/'
            ],
        ], [
            'password.required' => 'Kata sandi tidak boleh kosong.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi minimal ada huruf besar, huruf kecil, angka, simbol.',
        ]);

        if (!Auth::guard('web')->validate(['email' => $request->user()->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'password' => __('Kata sandi ini tidak cocok dengan yang tersimpan'),
            ]);
        }
        $request->session()->put('auth.password_confirmed_at', time());
        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
