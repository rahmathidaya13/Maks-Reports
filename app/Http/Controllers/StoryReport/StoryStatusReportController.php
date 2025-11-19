<?php

namespace App\Http\Controllers\StoryReport;

use App\Traits\StoryReportValidation;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StoryStatusReportModel;
use App\Repositories\StoryStatusReportRepository;

class StoryStatusReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use StoryReportValidation;
    protected $storyReportRepository;
    public function __construct(StoryStatusReportRepository $storyReportRepository)
    {
        $this->storyReportRepository = $storyReportRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'limit',
            'page',
            'order_by',
            'start_date',
            'end_date',
        ]);
        $storyReport = $this->storyReportRepository->getCached(auth()->id(), $filters);
        return Inertia::render('StoryStatusReport/Index', compact('storyReport', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        $this->validationText($request->all());
        $storyReport = new StoryStatusReportModel();
        $storyReport->created_by = auth()->id();
        $storyReport->report_date = now()->toDateString();
        $storyReport->report_time = $request->input('report_time');
        $storyReport->count_status = $request->integer('count_status');
        $storyReport->description = $request->input('description');
        $storyReport->save();
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')->with('message', 'Laporan Update Status harian kamu berhasil dibuat');
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
        $storyReport = $storyStatusReportModel::findOrFail($id);
        $this->storyReportRepository->clearCache(auth()->id());
        return Inertia::render('StoryStatusReport/Form/pageForm', compact('storyReport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoryStatusReportModel $storyStatusReportModel, string $id)
    {
        $this->validationText($request->all());
        $storyReport = $storyStatusReportModel::findOrFail($id);
        $storyReport->created_by = auth()->id();
        $storyReport->report_date = now()->toDateString();
        $storyReport->report_time = $request->input('report_time');
        $storyReport->count_status = $request->integer('count_status');
        $storyReport->description = $request->input('description');
        $storyReport->update();
        $this->storyReportRepository->clearCache(auth()->id());
        return redirect()->route('story_report')->with('message', 'Laporan Update Status harian kamu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoryStatusReportModel $storyStatusReportModel)
    {
        //
    }
}
