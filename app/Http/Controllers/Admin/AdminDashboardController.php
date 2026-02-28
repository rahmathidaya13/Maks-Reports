<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Repositories\AdminDashboardRepository;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['admin.access']);
    }
    public function index(AdminDashboardRepository $dashboardRepository)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        return Inertia::render(
            'Admin/Index',
            array_merge($dashboardRepository->getDashboard(auth()->id(), $startOfMonth, $endOfMonth))
        );
    }
    public function reset()
    {
        app(AdminDashboardRepository::class)->clearCache(auth()->id());
        return redirect()->route('admin.dashboard.index')->with('message', 'Data dashboard berhasil diperbarui.');
    }
}
