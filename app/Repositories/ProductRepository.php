<?php

namespace App\Repositories;

use App\Models\ProductPriceModel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'product_list_data';
    public function getData(array $filters = []): LengthAwarePaginator
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole(['admin', 'developer']);

        $query = ProductPriceModel::query()
            ->with(['creator', 'product', 'branch'])
            ->has('product');

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim string kosong '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== '' && $filters['keyword'] !== null) {
            $search = $filters['keyword'];
            // 1. Buat versi "bersih" dari input user (hapus semua tanda strip/dash)
            $cleanSearch = str_replace('-', '', $search);
            $query->whereHas('product', function ($product) use ($search, $cleanSearch) {
                $product->where(function ($q) use ($search, $cleanSearch) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereRaw("REPLACE(product_id, '-', '') LIKE ?", ["%{$cleanSearch}%"]);
                });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER CATEGORY (Vue kirim null)
    |--------------------------------------------------------------------------
    */
        if (array_key_exists('category', $filters) && $filters['category'] !== null) {
            $category = $filters['category'];

            $query->whereHas('product', function ($product) use ($category) {
                $product->where('category', $category);
            });
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER STATUS + ROLE (AMAN & EKSPLISIT)
    |--------------------------------------------------------------------------
    */
        if ($isAdmin) {
            // Admin:
            // Jika status dikirim (tidak null), pakai
            if (array_key_exists('status', $filters) && $filters['status'] !== null) {
                $query->where('status', $filters['status']);
            }
            // Jika null → tampilkan semua (draft + published)
        } else {
            // User biasa SELALU published
            $query->where('status', 'published');
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER BRANCH
    |--------------------------------------------------------------------------
    */
        if ($isAdmin) {
            // Admin boleh filter branch jika dikirim
            if (array_key_exists('branch', $filters) && $filters['branch'] !== null) {
                $branch = $filters['branch'];

                $query->whereHas('branch', function ($branchQuery) use ($branch) {
                    $branchQuery->where('name', $branch);
                });
            }
        } else {
            // User biasa dikunci ke branch miliknya
            $userBranch = optional($user->profile->branch)->name;

            if ($userBranch) {
                $query->whereHas('branch', function ($branchQuery) use ($userBranch) {
                    $branchQuery->where('name', $userBranch);
                });
            }
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER DISCOUNT
    |--------------------------------------------------------------------------
    */
        if (array_key_exists('discount_only', $filters) && $filters['discount_only'] !== null) {
            $query->where('price_type', $filters['discount_only']);
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER CONDITION
    |--------------------------------------------------------------------------
    */
        if (array_key_exists('condition', $filters) && $filters['condition'] !== null) {
            $condition = $filters['condition'];

            $query->whereHas('product', function ($product) use ($condition) {
                $product->where('item_condition', $condition);
            });
        }

        return $query
            ->orderBy('product_price.created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
