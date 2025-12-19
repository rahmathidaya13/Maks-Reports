<?php

namespace App\Policies;

use App\Models\StoryStatusReportModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StatusReportPolicy extends BasePolicy
{
    protected string $model = StoryStatusReportModel::class;
}
