<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseCacheRepository
{
    protected string $cachePrefix = 'repository_cache';
    protected int $cacheMinutes = 5;
    protected int $keyListHours = 6;
    protected int $maxKeys = 50;
    protected int $lockSeconds = 30;
    protected int $lockWaitSeconds = 15;

    /**
     * Method ini HARUS dioverride oleh child class untuk menjalankan query-nya.
     */

    abstract protected function getData(array $filters = []): LengthAwarePaginator;

    /**
     * Build cache key berdasarkan user dan filter
     */
    protected function buildCacheKey($userId, array $filters = []): string
    {
        return "{$this->cachePrefix}_{$userId}_" . md5(json_encode($filters));
        Log::info('Cache key built: ' . $cacheKey);
    }

    /**
     * Ambil data dengan caching aman (pakai lock)
     */
    public function getCached($userId, array $filters = []): LengthAwarePaginator
    {
        if (Config::get('cache.default') === 'array') {
            Log::warning('Cache driver is array; skipping cache for ' . static::class);
            return $this->getData($filters);
        }

        $cacheKey = $this->buildCacheKey($userId, $filters);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $lock = Cache::lock("lock_{$cacheKey}", $this->lockSeconds);
        $acquired = false;

        try {
            $lock->block($this->lockWaitSeconds);
            $acquired = true;

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $result = $this->getData($filters);
            Cache::put($cacheKey, $result, now()->addMinutes($this->cacheMinutes));

            $this->addKeyToUserList($userId, $cacheKey);

            return $result;
        } catch (LockTimeoutException $e) {
            Log::warning("Cache lock timeout for user {$userId}: " . $e->getMessage());
            return $this->getData($filters);
        } finally {
            if ($acquired) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    Log::warning('Failed to release cache lock: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Tambahkan cache key ke daftar key user.
     */
    protected function addKeyToUserList($userId, string $cacheKey): void
    {
        $keysListKey = "{$this->cachePrefix}_{$userId}_keys";
        $lock = Cache::lock("lock_{$keysListKey}", 5);
        $acquired = false;
        Log::info('Adding key to user list: ' . $cacheKey . ' for user ' . $userId . '...' . ' (key list: ' . $keysListKey . ')');
        try {
            $lock->block(3);
            $acquired = true;

            $keys = Cache::get($keysListKey, []);
            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                if (count($keys) > $this->maxKeys) {
                    $keys = array_slice($keys, -$this->maxKeys);
                }
                Cache::put($keysListKey, $keys, now()->addHours($this->keyListHours));
            }
        } finally {
            if ($acquired) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    Log::warning('Failed to release lock in addKeyToUserList: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Hapus semua cache milik user.
     */
    public function clearCache($userId): void
    {
        $keysListKey = "{$this->cachePrefix}_{$userId}_keys";
        $keys = Cache::get($keysListKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($keysListKey);
        Log::info('Cache cleared for user ' . $userId . ' (key list: ' . $keysListKey . ')');
    }
}
