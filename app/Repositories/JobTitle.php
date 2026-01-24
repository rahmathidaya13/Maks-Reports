<?php

namespace App\Repositories;

use App\Models\JobTitleModel;
use Illuminate\Pagination\LengthAwarePaginator;

class JobTitle extends BaseCacheRepository
{
    protected string $cachePrefix = 'job_title';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'developer']);

        $query = JobTitleModel::query()
            ->with('creator');

        /*
    |--------------------------------------------------------------------------
    | ROLE-BASED ACCESS
    |--------------------------------------------------------------------------
    */
        if (!$isAdmin) {
            // 🔒 User biasa hanya boleh melihat data miliknya
            $query->where('created_by', $user->id);
        }

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim string kosong '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== '' && $filters['keyword'] !== null) {
            $search = $filters['keyword'];

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('title_alias', 'like', "%{$search}%")
                    ->orWhere('job_title_code', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
