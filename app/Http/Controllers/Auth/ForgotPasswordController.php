<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Passwords/Email');
    }
    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users|max:50|min:5'], [
            'email.required' => 'Field email tidak boleh kosong.',
            'email.email' => 'Format email yang dimasukan tidak sesuai.',
            'email.exists' => 'Email tidak terdaftar di sistem.',
            'email.max' => 'Email maksimal 50 karakter.',
            'email.min' => 'Email minimal 5 karakter.',
        ]);

        // Menggunakan fitur bawaan Laravel untuk mengirim link reset
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('message', 'link atur ulang kata sandi telah dikirim ke email. Harap segera cek email Anda.');
        }
        return back()->with('error', 'Kami tidak dapat menemukan pengguna dengan email tersebut');
    }
}
