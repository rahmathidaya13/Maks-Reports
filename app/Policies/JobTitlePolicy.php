<?php

namespace App\Policies;

use App\Models\JobTitleModel;

class JobTitlePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */

    protected string $model = JobTitleModel::class;

}
