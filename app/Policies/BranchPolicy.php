<?php

namespace App\Policies;

use App\Models\BranchesModel;
use App\Models\User;

class BranchPolicy extends BasePolicy
{
    protected string $model = BranchesModel::class;
}
