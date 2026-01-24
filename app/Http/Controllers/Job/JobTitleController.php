<?php

namespace App\Http\Controllers\Job;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobTitleModel;
use App\Traits\JobTitleValidation;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Repositories\JobTitle;

class JobTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use JobTitleValidation;
    protected $jobTitle;
    public function __construct(JobTitle $jobTitles)
    {
        $this->jobTitle = $jobTitles;
        $this->middleware('role:developer|admin');
    }
    public function index(Request $request)
    {
        $this->authorize('view', JobTitleModel::class);
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $jobTitle = $this->jobTitle->getCached(auth()->id(), $filters);
        return Inertia::render('JobTitle/Index', compact('jobTitle', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', JobTitleModel::class);
        $uniqCode = JobTitleModel::generateUniqueCode();
        $this->jobTitle->clearCache(auth()->id());
        return Inertia::render('JobTitle/Form/pageForm', compact('uniqCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', JobTitleModel::class);
        $this->validationText($request->all());
        $jobTitle = new JobTitleModel();
        $jobTitle->created_by = auth()->id();
        $jobTitle->title = $request->input('title');
        $jobTitle->slug = Str::slug($request->input('title'));
        $jobTitle->title_alias = $request->input('title_alias');
        $jobTitle->description = $request->input('description');
        $jobTitle->save();
        $this->jobTitle->clearCache(auth()->id());
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $jobTitle->title . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobTitleModel $jobTitleModel)
    {
        $this->authorize('view', JobTitleModel::class);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobTitleModel $jobTitleModel, string $id)
    {
        $this->authorize('edit', JobTitleModel::class);
        $this->jobTitle->clearCache(auth()->id());
        $uniqCode = $jobTitleModel::generateUniqueCode();
        $jobTitle = $jobTitleModel::findOrFail($id);
        return Inertia::render('JobTitle/Form/pageForm', compact('uniqCode', 'jobTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobTitleModel $jobTitleModel, string $id)
    {
        $this->authorize('edit', JobTitleModel::class);
        $this->validationText($request->all(), $id);
        $jobTitle = $jobTitleModel::findOrFail($id);
        $jobTitle->created_by = auth()->id();
        $jobTitle->title = $request->input('title');
        $jobTitle->slug = Str::slug($request->input('title'));
        $jobTitle->title_alias = $request->input('title_alias');
        $jobTitle->description = $request->input('description');
        $jobTitle->update();
        $this->jobTitle->clearCache(auth()->id());
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $jobTitle->title . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobTitleModel $jobTitleModel, string $id)
    {
        $this->authorize('delete', JobTitleModel::class);
        $jobTitle = $jobTitleModel::findOrFail($id);
        $jobTitle->delete();
        $this->jobTitle->clearCache(auth()->id());
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $jobTitle->title . ' berhasil dihapus.');
    }
    public function destroy_all(Request $request)
    {
        $this->authorize('delete', JobTitleModel::class);
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');
        JobTitleModel::whereIn('job_title_id', $all_id)->delete();
        $this->jobTitle->clearCache(auth()->id());
        return redirect()->route('job_title')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }

    public function reset()
    {
        $this->jobTitle->clearCache(auth()->id());
        return redirect()->route('job_title')->with('message', 'Data Jabatan berhasil diperbarui.');
    }
}
