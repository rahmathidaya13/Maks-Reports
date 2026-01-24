<?php

namespace App\Repositories;

use App\Models\CustomerModel;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'customers_cache_';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $query = CustomerModel::query()
            ->with('creator')
            ->where('created_by', auth()->id())
            ->whereNull('deleted_at');

        /*
    |--------------------------------------------------------------------------
    | FILTER KEYWORD (Vue kirim string kosong '')
    |--------------------------------------------------------------------------
    */
        if (isset($filters['keyword']) && $filters['keyword'] !== '' && $filters['keyword'] !== null) {
            $search = $filters['keyword'];

            $query->where(function ($q) use ($search) {
                $q->where('national_id_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('number_phone_customer', 'like', "%{$search}%")
                    ->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
