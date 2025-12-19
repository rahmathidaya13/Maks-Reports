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
        $query = CustomerModel::with('creator')
            ->where('created_by', auth()->id())
            ->whereNull('deleted_at')
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('national_id_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('number_phone_customer', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
