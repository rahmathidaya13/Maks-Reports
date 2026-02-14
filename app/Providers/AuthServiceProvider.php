<?php

namespace App\Providers;

use App\Models\User;
use App\Models\ProductModel;
use App\Models\SalesRecords;
use App\Models\BranchesModel;
use App\Models\CustomerModel;
use App\Models\JobTitleModel;
use App\Policies\BranchPolicy;
use App\Policies\ProductPolicy;
use App\Models\DailyReportModel;
use App\Models\TransactionModel;
use App\Policies\CustomerPolicy;
use App\Policies\JobTitlePolicy;
use App\Models\ProductPriceModel;
use App\Policies\TransactioPolicy;
use App\Policies\SalesRecordPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\StatusReportPolicy;
use Illuminate\Support\Facades\Gate;
use App\Models\StoryStatusReportModel;
use App\Policies\DailyReportLeadsPolicy;
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
        TransactionModel::class => TransactionPolicy::class,
        ProductModel::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
