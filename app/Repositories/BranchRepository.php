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
        $query = BranchesModel::with(['creator', 'branchPhone'])
            ->when(
                ! $user->hasAnyRole(['developer', 'admin']),
                fn($q) => $q->where('created_by', $user->id) // 🔒 Batasi hanya untuk user biasa
            )
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhereHas('branchPhone', function ($phone) use ($search) {
                            $phone->where('phone', 'like', "%{$search}%");
                        });
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
