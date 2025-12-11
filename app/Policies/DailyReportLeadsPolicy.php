<?php

namespace App\Policies;

use App\Models\DailyReportModel;
class DailyReportLeadsPolicy extends BasePolicy
{
    // Tentukan model yang akan digunakan dalam policy ini
    protected string $model = DailyReportModel::class;
}
