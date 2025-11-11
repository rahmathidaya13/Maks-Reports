<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerificationController extends Controller
{
    public function notice()
    {
        $email = auth()->user()?->email;
        return Inertia::render('Auth/Verify', compact('email'));
    }

    public function verify(EmailVerificationRequest $request)
    {
        auth()->user()->update([
            'is_active' => true,
            'first_login' => now(),
        ]);
        $request->fulfill();
        return redirect('/home')->with('message', 'Email Anda berhasil diverifikasi. Selamat datang!');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/home')->with('message', 'Email Anda sudah diverifikasi.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Kami telah mengirim ulang tautan verifikasi ke email Anda. Silakan cek email Anda.');
    }
}
