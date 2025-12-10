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
        $this->authorize('view', DailyReportModel::class);
        $filters = $request->only([
            'limit',
            'page',
            'order_by',
            'start_date',
            'end_date',
        ]);
        $dailyReport = $this->dailyReports->getCached(auth()->id(), $filters);
        // $can_search = auth()->user()->hasAnyRole(['admin', 'super-admin', 'developer', 'editor']);
        return Inertia::render('DailyReport/Index', compact('dailyReport', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', DailyReportModel::class);
        $this->dailyReports->clearCache(auth()->id());
        return Inertia::render('DailyReport/Form/pageForm', [
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', DailyReportModel::class);
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
        $dailyReport->save();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('message', 'Laporan Leads harian kamu berhasil dibuat');
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
        $this->authorize('update', DailyReportModel::class);
        $dailyReport = $dailyReportModel::findOrFail($id);
        $this->dailyReports->clearCache(auth()->id());
        return Inertia::render('DailyReport/Form/pageForm', [
            'dailyReport' => $dailyReport,
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyReportModel $dailyReportModel, string $id)
    {
        $this->authorize('update', DailyReportModel::class);
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
        $dailyReport->update();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('message', 'Laporan Leads harian kamu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReportModel $dailyReportModel, string $id)
    {
        $this->authorize('delete', DailyReportModel::class);
        $dailyReport = $dailyReportModel::find($id);
        $dailyReport->delete();
        $this->dailyReports->clearCache(auth()->id());
        return redirect()->route('daily_report')->with('message', 'Laporan Leads harian kamu berhasil dihapus');
    }

    public function destroy_all(DailyReportModel $dailyReportModel, Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id))
            return back()->with('message', 'Tidak ada data yang dipilih.');

        $this->dailyReports->clearCache(auth()->id());

        $dailyReportModel::whereIn('daily_report_id', $all_id)->delete();
        return redirect()->route('daily_report')->with('message', count($all_id) . ' Data Leads berhasil Terhapus.');
    }
}
