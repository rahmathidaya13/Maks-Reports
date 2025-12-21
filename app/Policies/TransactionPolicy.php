<?php

namespace App\Policies;

use App\Models\TransactionModel;

class TransactionPolicy extends BasePolicy
{
    protected string $model = TransactionModel::class;
}
