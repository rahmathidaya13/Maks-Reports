<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'job_title';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $user = auth()->user();
        $query = TransactionModel::query()
            ->with(['creator', 'customer', 'product', 'payments'])
            ->where('created_by', $user->id)
            ->when(!empty($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status'] ?? 'first_payment');
            })
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->whereHas('customer', function ($customer) use ($search) {
                    $customer->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('number_phone_customer', 'like', "%{$search}%");
                });
                $q->orWhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
