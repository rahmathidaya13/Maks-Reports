<?php

namespace App\Providers;

use App\Models\BranchesModel;
use App\Models\DailyReportModel;
use App\Models\JobTitleModel;
use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\DailyReportLeadsPolicy;
use App\Policies\JobTitlePolicy;
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
        DailyReportModel::class => DailyReportLeadsPolicy::class,
        JobTitleModel::class => JobTitlePolicy::class,
        BranchesModel::class => BranchPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
