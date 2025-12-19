<?php

namespace App\Policies;

use App\Models\SalesRecords;

class SalesRecordPolicy extends BasePolicy
{
    protected string $model = SalesRecords::class;
}
