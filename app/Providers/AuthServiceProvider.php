<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('view', fn(User $user) => $user->can_view);
        Gate::define('add', fn(User $user) => $user->can_add);
        Gate::define('update',  fn(User $user) => $user->can_edit);
        Gate::define('delete', fn(User $user) => $user->can_delete);
        Gate::define('share',  fn(User $user) => $user->can_share);
        Gate::define('upload',  fn(User $user) => $user->can_upload);
        Gate::define('import', fn(User $user) => $user->can_import);
        Gate::define('export', fn(User $user) => $user->can_export);
        Gate::define('onlyAdmin', fn(User $user) => $user->role === 'admin');
        Gate::define('onlyDeveloper',  fn(User $user) => $user->role === 'developer');
        Gate::define('manage-backup',  fn(User $user) => in_array($user->role, ['admin', 'developer']));
    }
}
