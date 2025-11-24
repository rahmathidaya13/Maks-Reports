<?php

namespace App\Http\Controllers\Home;

use App\Traits\DashboardSummaryStatusReport;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    use DashboardSummaryStatusReport;
    public function __construct()
    {
        $this->middleware('role:developer|admin|super-admin|editor|user');
    }
    public function index()
    {
        return Inertia::render('Home/Index', [
            'summaryStatusReport' => $this->dashboardSummary(),
        ]);
    }
}
