<?php

namespace App\Http\Controllers\StoryReport;

use App\Traits\DashboardSummaryStatusReport;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Traits\StoryReportValidation;
use App\Models\StoryStatusReportModel;
use App\Repositories\StoryStatusReportRepository;

class StoryStatusReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use StoryReportValidation, DashboardSummaryStatusReport;
    protected $storyReportRepository;
    public function __construct(StoryStatusReportRepository $storyReportRepository)
    {
        $this->storyReportRepository = $storyReportRepository;
    }
    public function index(Request $request)
    {
        $this->authorize('view', StoryStatusReportModel::class);
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
            'start_date',
            'end_date',
        ]);
        $storyReport = $this->storyReportRepository->getCached(auth()->id(), $filters);

        // Hitung total status di hari ini
        $totalToday = StoryStatusReportModel::where('created_by', auth()->id())
            ->whereDate('report_date', now()->toDateString())
            ->sum('count_status');
        return Inertia::render('StoryStatusReport/Index', [
            'storyReport' => $storyReport,
            'totalToday' => (int) $totalToday,
            'filters' => $filters,
            'summary' => $this->dashboardSummary()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', StoryStatusReportModel::class);
        $this->storyReportRepository->clearCache(auth()->id());
        return Inertia::render('StoryStatusReport/Form/pageForm', [
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', StoryStatusReportModel::class);
        $this->validationText($request->all());
        $reports = $request->input('report', []);
        $createdReportById = [];
        foreach ($reports as $report) {
            $story = StoryStatusReportModel::create([
                'created_by' => auth()->id(),
                'report_date' => now()->toDateString(),
                'report_time' => $report['report_time'],
                'count_status' => $report['count_status'],
            ]);
            $createdReportById[] = $story->story_status_id;
        }
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')
            ->with('message', count($createdReportById) . ' ' . 'Laporan Update Status harian berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoryStatusReportModel $storyStatusReportModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoryStatusReportModel $storyStatusReportModel, string $id)
    {
        $this->authorize('edit', StoryStatusReportModel::class);
        $storyReport = $storyStatusReportModel::findOrFail($id);
        $this->storyReportRepository->clearCache(auth()->id());
        return Inertia::render('StoryStatusReport/Form/pageForm', [
            'storyReport' => $storyReport,
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoryStatusReportModel $storyStatusReportModel, string $id)
    {
        $this->authorize('edit', StoryStatusReportModel::class);
        $data = $this->validationText($request->all());
        $storyReport = $storyStatusReportModel::findOrFail($id);
        $storyReport->created_by = auth()->id();
        $storyReport->report_date = now()->toDateString();
        $storyReport->report_time = $data['report'][0]['report_time'];
        $storyReport->count_status = $data['report'][0]['count_status'];
        $storyReport->update();
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')
            ->with('message', 'Laporan Update Status harian kamu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoryStatusReportModel $storyStatusReportModel, string $id)
    {
        $this->authorize('delete', StoryStatusReportModel::class);
        $storyReport = $storyStatusReportModel::find($id);
        $storyReport->delete();
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')->with('message', 'Laporan update status pada tanggal ' . Carbon::parse($storyReport->report_date)->translatedFormat('d F Y') . ' berhasil Terhapus.');
    }

    public function destroy_all(StoryStatusReportModel $storyStatusReportModel, Request $request)
    {
        $this->authorize('delete', StoryStatusReportModel::class);
        $all_id = $request->input('ids', []);
        if (!count($all_id)) {
            return back()->with('message', 'Tidak ada data yang dipilih.');
        }
        $storyStatusReportModel::whereIn('story_status_id', $all_id)->delete();
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }

    public function reset()
    {
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')->with('message', 'Data laporan Update Status harian berhasil diperbarui');
    }
}
