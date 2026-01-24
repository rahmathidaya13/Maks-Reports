<?php

namespace App\Repositories;

use App\Models\BranchesModel;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'branches_cache';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['developer', 'admin']);

        $query = BranchesModel::query()
            ->with(['creator', 'branchPhone']);

        /*
    |--------------------------------------------------------------------------
    | ROLE-BASED ACCESS
    |--------------------------------------------------------------------------
    */
        if (!$isAdmin) {
            // 🔒 User biasa hanya boleh lihat data miliknya
            $query->where('created_by', $user->id);
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim string kosong '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== ''  && $filters['keyword'] !== null) {
            $search = $filters['keyword'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('branchPhone', function ($phone) use ($search) {
                        $phone->where('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
