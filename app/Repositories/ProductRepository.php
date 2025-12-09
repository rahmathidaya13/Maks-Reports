<?php

namespace App\Repositories;

use App\Models\ProductModel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'product_list_data';
    public function getData(array $filters = []): LengthAwarePaginator
    {
        $query = ProductModel::query()
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['category']), function ($q) use ($filters) {
                $category = $filters['category'];
                $q->where('category', $category);
            });
        $product = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        return $product;
    }
}
