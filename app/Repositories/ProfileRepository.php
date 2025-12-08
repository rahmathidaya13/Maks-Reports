<?php

namespace App\Repositories;
use App\Models\ProfileModel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfileRepository extends BaseCacheRepository
{
    protected string $cachePrefix = 'profile_cache';
    protected function getData(array $filters = []): LengthAwarePaginator
    {
        $user = $filters['users_id'];
        $profile = ProfileModel::with(['user', 'branch', 'jobTitle'])
            ->where('users_id', $user)
            ->first();
        return new LengthAwarePaginator([$profile], 1, 1);
    }
}