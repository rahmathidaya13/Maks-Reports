<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccess
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
        // Jika user BUKAN user atau developer, TENDANG KELUAR ke dashboard user + kirim Alert
        if (!$user->hasAnyRole(['user', 'developer'])) {
            // kembalikan ke halaman dashboard admin
            return redirect()->route('admin.dashboard.index')
                ->with('error', 'Akses Ditolak! Anda tidak memiliki izin untuk membuka halaman ini.');
        }
        return $next($request);
    }
}
