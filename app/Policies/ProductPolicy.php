<?php

namespace App\Policies;

use App\Models\ProductModel;
use App\Models\ProductPriceModel;

class ProductPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    protected string $model = ProductModel::class;
}
