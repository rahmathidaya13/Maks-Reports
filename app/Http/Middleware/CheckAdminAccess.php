<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        // Jika user BUKAN admin atau developer, TENDANG KELUAR ke dashboard user + kirim Alert
        if (!$user->hasAnyRole(['admin', 'developer'])) {
            return redirect()->route('home')
                ->with('error', 'Akses Ditolak! Anda tidak memiliki izin untuk membuka halaman ini.');
        }
        return $next($request);
    }
}
