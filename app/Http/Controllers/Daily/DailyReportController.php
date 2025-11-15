<?php

namespace App\Http\Controllers\Daily;

use App\Traits\DailyReport;
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
    use DailyReport;
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
        $this->dailyReports->clearCache(auth()->id());
        return Inertia::render('DailyReport/Form/pageForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validationText($request->all());
        $dailyReport = new DailyReportModel();
        $dailyReport->created_by = auth()->id();
        $dailyReport->date = now()->toDateString();
        $dailyReport->leads = $request->integer('leads');
        $dailyReport->closing = $request->integer('closing');
        $dailyReport->fu_yesterday = $request->integer('fu_yesterday');
        $dailyReport->fu_yesterday_closing = $request->integer('fu_yesterday_closing');
        $dailyReport->fu_before_yesterday = $request->integer('fu_before_yesterday');
        $dailyReport->fu_before_yesterday_closing = $request->integer('fu_before_yesterday_closing');
        $dailyReport->fu_last_week = $request->integer('fu_last_week');
        $dailyReport->fu_last_week_closing = $request->integer('fu_last_week_closing');
        $dailyReport->engage_old_customer = $request->integer('engage_old_customer');
        $dailyReport->engage_closing = $request->integer('engage_closing');
        $dailyReport->notes = $request->input('notes');
        $dailyReport->save();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('success', 'Laporan harian kamu berhasil dibuat');
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
    public function edit(DailyReportModel $dailyReportModel, string $id)
    {
        $dailyReport = $dailyReportModel::findOrFail($id);
        $this->dailyReports->clearCache(auth()->id());
        return Inertia::render('DailyReport/Form/pageForm', compact('dailyReport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyReportModel $dailyReportModel, string $id)
    {
        $this->validationText($request->all());
        $dailyReport = $dailyReportModel::findOrFail($id);
        $dailyReport->created_by = auth()->id();
        $dailyReport->date = now()->toDateString();
        $dailyReport->leads = $request->integer('leads');
        $dailyReport->closing = $request->integer('closing');
        $dailyReport->fu_yesterday = $request->integer('fu_yesterday');
        $dailyReport->fu_yesterday_closing = $request->integer('fu_yesterday_closing');
        $dailyReport->fu_before_yesterday = $request->integer('fu_before_yesterday');
        $dailyReport->fu_before_yesterday_closing = $request->integer('fu_before_yesterday_closing');
        $dailyReport->fu_last_week = $request->integer('fu_last_week');
        $dailyReport->fu_last_week_closing = $request->integer('fu_last_week_closing');
        $dailyReport->engage_old_customer = $request->integer('engage_old_customer');
        $dailyReport->engage_closing = $request->integer('engage_closing');
        $dailyReport->notes = $request->input('notes');
        $dailyReport->update();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('message', 'Laporan harian kamu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReportModel $dailyReportModel, string $id)
    {
        $dailyReport = $dailyReportModel::find($id);
        $dailyReport->delete();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('message', 'Laporan harian kamu berhasil dihapus');
    }
}
