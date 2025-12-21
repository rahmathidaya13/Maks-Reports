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
            ->with([
                'creator:id,name',
                'customer:customer_id,customer_name,number_phone_customer',
                'product:product_id,name',
                'payments:payment_id,transaction_id,payment_date,payment_type,amount'
            ])
            ->withSum('payments as total_paid', 'amount')
            ->where('created_by', $user->id)
            ->when(!empty($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status'] ?? 'first_payment');
            })
            ->when(!empty($filters['date_filter']), function ($q) use ($filters) {
                $q->whereDate('transaction_date', $filters['date_filter']);
            })
            ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $search = $filters['keyword'];
                $q->where(function ($qq) use ($search) {
                    $qq->whereHas('customer', function ($customer) use ($search) {
                        $customer->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('number_phone_customer', 'like', "%{$search}%");
                    })
                        ->orWhereHas('product', function ($product) use ($search) {
                            $product->where('name', 'like', "%{$search}%");
                        });
                });
            });

        return $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
            ->paginate($filters['limit'] ?? 10);
    }
}
