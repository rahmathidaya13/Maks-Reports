<?php

namespace App\Http\Controllers\Home;

use App\Repositories\DashboardRepository;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['user.access']);
    }
    public function index(Request $request, DashboardRepository $dashboardRepository)
    {
        // Default bulan ini, atau filter dari request
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);


        return Inertia::render(
            'Home/Index',
            array_merge(
                $dashboardRepository->getDashboard(auth()->id(), $month, $year),
                ['filters' => compact('month', 'year')]
            )
        );
    }

    public function reset()
    {
        app(DashboardRepository::class)->clearCache(auth()->id());
        return redirect()->route('home')->with('message', 'Data dashboard berhasil diperbarui.');
    }
}
