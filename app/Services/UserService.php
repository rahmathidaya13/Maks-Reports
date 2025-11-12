<?php

namespace App\Services;

use App\Models\User;
use App\Events\UserStatusUpdated;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function checkAndBroadcastStatus()
    {
        $time = now()->subMinute(1);
        // cek apakah ada user baru
        $newUserRegistered =  User::where('created_at', '>=', $time)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'name', 'email']);
        // cek user yang baru login
        $recentlyLogin = User::where('is_active', true)
            ->where('updated_at', '>=', $time)
            ->count();
        // cek user yang baru logout
        $recentlyLogout = User::where('is_active', false)
            ->where('updated_at', '>=', $time)
            ->count();


        if ($newUserRegistered->isNotEmpty()) {
            foreach ($newUserRegistered as $user) {
                // contoh: broadcast nama user
                $statusData['new_user'][] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }
        }
        $statusData = [
            'new_user_registered' => $newUserRegistered->isNotEmpty(),
            'new_user_name' => $newUserRegistered->map(fn($u) => $u->name),
            'recently_logged_out' => $recentlyLogout,
            'recently_logged_in' => $recentlyLogin,
        ];



        broadcast(new UserStatusUpdated($statusData));
        Log::info('User status updated: ' . 'user login:' . $statusData['recently_logged_in'] . ' ' . 'user logout:' . $statusData['recently_logged_out'] . ' ' . 'new user registered: ' . $statusData['new_user_registered']);
        return $statusData;
    }
}
