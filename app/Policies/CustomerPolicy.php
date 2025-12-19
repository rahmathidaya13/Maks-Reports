<?php

namespace App\Policies;

use App\Models\CustomerModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy extends BasePolicy
{
    protected string $model = CustomerModel::class;
}
