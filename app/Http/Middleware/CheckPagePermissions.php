<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $action = 'can_view'): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $routeName = Route::currentRouteName();
        if (!$routeName) {
            // if route has no name, optionally accept slug parameter or abort
            abort(403, 'Route tidak memiliki nama. Konfigurasi route name diperlukan.');
        }
        $pageSlug = explode('.', $routeName)[0];
        if (!$user->hasPagePermission($pageSlug, $action)) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }
        return $next($request);
    }
}
