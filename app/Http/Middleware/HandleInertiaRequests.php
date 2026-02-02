<?php

namespace App\Http\Middleware;

use App\Models\ProductRequestUserModel;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => fn() => [
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'profile' => auth()->user()->profile->load('branch', 'jobTitle'),
                    'roles' => auth()->user()->getRoleNames(),
                    'permissions' => auth()->user()?->getPermissionsViaRoles()->pluck('name')

                ] : null,
            ],
            'flash' => fn() => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
                'message' => $request->session()->get('message'),
                'status' => $request->session()->get('status'),
                'highlight_by_id' => $request->session()->get('highlight_by_id'),
                'highlight_type' => $request->session()->get('highlight_type'),
            ],
            'pending_request_count' => function () {
                if (auth()->check()) {
                    return ProductRequestUserModel::where('status', 'pending')->count();
                }
                return 0;
            },
            'old' => fn() => session()->getOldInput(),
            'path' => fn() => $request->path(),
            'fullUrl' => fn() => $request->fullUrl(),
            'app' => [
                'name' => config('app.name'),
                'version' => '1.0.0',
            ],
            'locale' => fn() => app()->getLocale(),
            'query' => fn() => $request->query(),
        ]);
    }
}
