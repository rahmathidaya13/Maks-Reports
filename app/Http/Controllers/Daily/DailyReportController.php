<?php

namespace App\Http\Controllers\Daily;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\DailyReportModel;
use App\Http\Controllers\Controller;
use App\Repositories\DailyReportsRepository;

class DailyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $dailyReports;
    public function __construct(DailyReportsRepository $dailyReportsRepository)
    {
        $this->dailyReports = $dailyReportsRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
            'start_date',
            'end_date',
        ]);
        $dailyReport = $this->dailyReports->getCached(auth()->id(), $filters);
        $can_search = auth()->user()->hasAnyRole(['admin', 'super-admin', 'developer', 'editor']);
        return Inertia::render('DailyReport/Index', compact('dailyReport', 'filters', 'can_search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyReportModel $dailyReportModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyReportModel $dailyReportModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyReportModel $dailyReportModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReportModel $dailyReportModel)
    {
        //
    }
}
