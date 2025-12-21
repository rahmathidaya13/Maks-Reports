<?php

namespace App\Providers;

use App\Models\BranchesModel;
use App\Models\CustomerModel;
use App\Models\DailyReportModel;
use App\Models\JobTitleModel;
use App\Models\SalesRecords;
use App\Models\StoryStatusReportModel;
use App\Models\TransactionModel;
use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DailyReportLeadsPolicy;
use App\Policies\JobTitlePolicy;
use App\Policies\SalesRecordPolicy;
use App\Policies\StatusReportPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\TransactioPolicy;
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
        StoryStatusReportModel::class => StatusReportPolicy::class,
        CustomerModel::class => CustomerPolicy::class,
        SalesRecords::class => SalesRecordPolicy::class,
        TransactionModel::class => TransactionPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
