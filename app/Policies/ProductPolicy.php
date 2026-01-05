<?php

namespace App\Policies;

use App\Models\ProductPriceModel;

class ProductPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    protected string $model = ProductPriceModel::class;
}
