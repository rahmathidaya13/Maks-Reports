<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProfileMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user()->profile;
        if (!$user->is_completed) {
            // Cegah akses ke halaman lain kecuali halaman profile jika belum lengkap
            if (!$request->routeIs('profile', 'profile.update')) {
                return redirect()->route('profile')
                    ->with('info', 'Lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
            }
        }
        return $next($request);
    }
}
