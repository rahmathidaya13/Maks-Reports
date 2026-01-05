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
            ->has('product')
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
            })

            // === [PERUBAHAN LOGIC STATUS & ROLE DI SINI] ===
            ->where(function ($q) use ($filters) {
                $user = auth()->user();

                // Pastikan nama role sesuai dengan database Spatie kamu (misal: 'admin' atau 'Super Admin')
                $isAdmin = $user && $user->hasRole(['admin', 'developer']);
                if ($isAdmin) {
                    // LOGIKA ADMIN:
                    // Jika Admin memilih filter status tertentu, turuti.
                    if (!empty($filters['status'])) {
                        $q->where('status', $filters['status']);
                    }
                    // Jika filter kosong, Admin berhak melihat SEMUA (Draft + Published).
                    // Jadi tidak perlu 'else' untuk where.
                } else {
                    // LOGIKA USER BIASA:
                    // User biasa HANYA boleh melihat published.
                    // Kita kunci (Hardcode) di sini demi keamanan.
                    $q->where('status', 'published');
                }
            })

            // Filter berdasarkan cabang
            ->where(function ($q) use ($filters) {
                $isAdmin = auth()->user() && auth()->user()->hasRole(['admin', 'developer']);
                $branch = $filters['branch'] ?? null;

                if (!$isAdmin) {
                    // User biasa hanya melihat produk dari cabang mereka
                    $userBranch = auth()->user()->profile->branch->name ?? null;
                    $q->whereHas('branch', function ($branchQuery) use ($userBranch) {
                        $branchQuery->where('name', $userBranch ?? 'all');
                    });
                } elseif (!empty($branch)) {
                    // Admin bisa filter berdasarkan cabang
                    $q->whereHas('branch', function ($branchQuery) use ($branch) {
                        $branchQuery->where('name', $branch ?? 'all');
                    });
                }
            })

            ->when(!empty($filters['discount_only']), function ($q) use ($filters) {
                $discount = $filters['discount_only'];
                $q->where('price_type', $discount ?? 'all');
            });


        $product = $query->orderBy('product_price.created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);

        return $product;
    }
}
