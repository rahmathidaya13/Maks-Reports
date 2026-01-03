<?php

namespace App\Repositories;

use App\Models\ProductModel;
use App\Models\ProductPriceModel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'product_list_data';
    public function getData(array $filters = []): LengthAwarePaginator
    {
        $query = ProductPriceModel::query()
            ->with(['creator', 'product', 'branch'])
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->whereHas('product', function ($product) use ($search) {
                    $product->where('name', 'like', "%{$search}%");
                });
            })

            ->when(!empty($filters['category']), function ($q) use ($filters) {
                $category = $filters['category'];
                $q->whereHas('product', function ($product) use ($category) {
                    $product->where('category', $category);
                });
            });
        $product = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        return $product;
    }
}
