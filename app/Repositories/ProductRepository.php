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
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole(['admin', 'developer']);

        $query = ProductModel::query()
            ->with(['creator', 'prices.branch']);

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim string kosong '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== '' && $filters['keyword'] !== null) {
            $search = $filters['keyword'];
            // 1. Buat versi "bersih" dari input user (hapus semua tanda strip/dash)
            $cleanSearch = str_replace('-', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(product_id, '-', '') LIKE ?", ["%{$cleanSearch}%"]);
            });
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER CATEGORY (Vue kirim null)
    |--------------------------------------------------------------------------
    */
        if (array_key_exists('category', $filters) && $filters['category'] !== null) {
            $category = $filters['category'];
            $query->where('category', $category);
        }


        if (array_key_exists('condition', $filters) && $filters['condition'] !== null) {
            $condition = $filters['condition'];
            $query->where('item_condition', $condition);
        }

        return $query
            ->orderBy('products.created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
