<?php

namespace App\Services;

use App\Models\User;
use App\Events\UserStatusUpdated;

class UserService
{
    public function checkAndBroadcastStatus()
    {
        $time = now()->subMinute(1);
        // cek apakah ada user baru
        $newUserRegistered = User::where('created_at', '>=', $time)->count();
        // cek user yang baru login
        $recentlyLogin = User::where('is_active', true)
            ->where('updated_at', '>=', $time)
            ->count();
        // cek user yang baru logout
        $recentlyLogout = User::where('is_active', false)
            ->where('updated_at', '>=', $time)
            ->count();

        $statusData = [
            'new_user_registered' => $newUserRegistered > 0,
            'recently_logged_out' => $recentlyLogout,
            'recently_logged_in' => $recentlyLogin,
        ];

        broadcast(new UserStatusUpdated($statusData));
    }
}
