<?php

namespace App\Http\Middleware;

use App\Models\ProfileModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $pageSlug, $action = 'can_view'): Response
    {
        // dd(auth()->user()->profile->role->name === 'teknisi');
        $user = $request->user()->profile->role;
        if (!$user || !$request->user()->profile->role) {
            return redirect()->route('login')->with('error', 'Anda belum memiliki akses!');
        }

        $hasPermission = ProfileModel::with('role')
            ->where('users_id', $request->user()->id)
            ->whereHas(
                'role.permissions',
                fn($q) =>
                $q->where('slug', $pageSlug)
                    ->where($action, true)
            )
            ->exists();
        // dd($hasPermission, $request->user()->id);
        if (!$hasPermission) {
            return redirect()->route('home')->with('error', 'Anda belum memiliki akses!');
        }

        return $next($request);
    }
}
