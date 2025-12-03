<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // middleware ini untuk cek pada profile yang
        // tujuannya agar user tidak bisa akses url yang bukan otoritasnya
        $profileById = $request->route("id");
        if ($profileById != auth()->user()->id) {
            return redirect()->route("profile.detail", auth()->user()->id)->with("error", "Akses anda tidak diperbolehkan!");
        }
        return $next($request);
    }
}
