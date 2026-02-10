<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'transaction_cache_';
    /**
     * Override: definisikan cara ambil data dari database
     */
    protected function getData(array $filters = []): LengthAwarePaginator
    {

        $query = TransactionModel::query()
            ->with([
                'creator:id,name',
                'customer:customer_id,customer_name,number_phone_customer',
                'payments:payment_id,transaction_id,payment_date,payment_type,amount,payment_method',
            ])
            ->with(['items.product'])
            ->withCount('items')
            ->withSum('payments as total_paid', 'amount')
            ->where('created_by', auth()->id())
            ->when($filters['status'] ?? null, function ($q, $status) {
                if ($status === 'all') {
                    return $q;
                }
                return $q->where('status', $status);
            }, function ($q) {
                return $q->whereIn('status', ['payment', 'repayment']);
            })
            ->when(
                isset($filters['date_filter']) && $filters['date_filter'] !== null,
                fn($q) => $q->whereDate('transaction_date', $filters['date_filter'])
            )
            ->when(
                isset($filters['keyword']) && $filters['keyword'] !== null && $filters['keyword'] !== '',
                function ($q) use ($filters) {
                    $search = $filters['keyword'];

                    $q->where(function ($qq) use ($search) {
                        $qq->whereHas('customer', function ($customer) use ($search) {
                            $customer->where('customer_name', 'like', "%{$search}%");
                        })
                            ->orWhere('invoice', 'like', "%{$search}%");
                    });
                }
            );

        return $query
            ->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
