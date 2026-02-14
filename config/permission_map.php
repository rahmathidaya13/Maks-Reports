<?php

use App\Models\BranchesModel;
use App\Models\CustomerModel;
use App\Models\DailyReportModel;
use App\Models\JobTitleModel;
use App\Models\ProductModel;
use App\Models\ProductPriceModel;
use App\Models\SalesRecords;
use App\Models\StoryStatusReportModel;
use App\Models\TransactionModel;

return [
    DailyReportModel::class => 'daily.report.leads',
    StoryStatusReportModel::class => 'status.report',
    SalesRecords::class => 'sales.record',
    BranchesModel::class => 'branches',
    JobTitleModel::class => 'job.title',
    ProductModel::class => 'product',
    CustomerModel::class => 'customers',
    TransactionModel::class => 'transaction',
    ProductModel::class => 'product',
];
