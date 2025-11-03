<?php

namespace App\Repositories;

use App\Models\JobTitleModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class RolesRepository
{
    protected $cacheKey = 'user_roles';

    public function getAllByUser($userId, $filters = [])
    {
        $cacheKey = "{$this->cacheKey}_{$userId}_" . md5(json_encode($filters));

        // Periksa apakah data sudah ada di cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Gunakan lock untuk mencegah race condition
        $lock = Cache::lock("lock_" . $cacheKey, 30);

        try {
            $lock->block(15); // Tunggu maksimal 15 detik

            // Periksa lagi setelah mendapatkan lock, jika proses lain sudah membuat cache
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Ambil data dari database
            $query = JobTitleModel::where('created_by', $userId)
                ->when(!empty($filters['keyword']), function ($q) use ($filters) {
                    $search = $filters['keyword'];
                    $q->where(function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                        $sub->where('job_title_code', 'like', "%{$search}%");
                        $sub->where('title_alias', 'like', "%{$search}%");
                    });
                });
            $result = $query->orderBy('created_at', $filters['order_by'] ?? 'desc')
                ->paginate($filters['limit'] ?? 10);

            // Simpan hasil ke cache
            Cache::put($cacheKey, $result, now()->addMinutes(5));

            // Tambahkan cacheKey ke daftar keys milik user
            $this->addKeyToUserList($userId, $cacheKey);

            return $result;
        } catch (LockTimeoutException $e) {
            return JobTitleModel::where('created_by', $userId)->paginate($filters['limit'] ?? 10);
        } finally {
            optional($lock)->release();
        }
    }

    private function addKeyToUserList($userId, $cacheKey)
    {
        $keysListKey = "{$this->cacheKey}_{$userId}_keys";
        $lock = Cache::lock("lock_" . $keysListKey, 5);
        try {
            $lock->block(3);
            $keys = Cache::get($keysListKey, []);
            if (!in_array($cacheKey, $keys)) {
                $keys[] = $cacheKey;
                Cache::put($keysListKey, $keys, now()->addHours(6));
            }
        } finally {
            optional($lock)->release();
        }
    }
    public function clearCache($userId)
    {
        $keysListKey = "{$this->cacheKey}_{$userId}_keys";
        $keys = Cache::get($keysListKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Hapus daftar key juga
        Cache::forget($keysListKey);
    }
}
