<?php

namespace App\Http\Controllers\Job;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobTitleModel;
use App\Traits\JobTitleValidation;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Repositories\RolesRepository;
use App\Http\Requests\FormRolesRequest;

class JobTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use JobTitleValidation;
    protected $rolesRepos;
    public function __construct(RolesRepository $rolesRepository)
    {
        $this->rolesRepos = $rolesRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $jobTitle = $this->rolesRepos->getAllByUser(auth()->id(), $filters);
        return Inertia::render('Roles/Index', compact('jobTitle', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->rolesRepos->clearCache(auth()->id());
        $uniqCode = JobTitleModel::generateUniqueCode();
        return Inertia::render('Roles/Form/pageForm', compact('uniqCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $this->validationText($request->all());
        $roles = new JobTitleModel();
        $roles->created_by = auth()->id();
        $roles->title = $request->input('title');
        $roles->slug = Str::slug($request->input('title'));
        $roles->title_alias = $request->input('title_alias');
        $roles->description = $request->input('description');
        $roles->save();
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $roles->title . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobTitleModel $jobTitleModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobTitleModel $jobTitleModel, string $id)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $uniqCode = $jobTitleModel::generateUniqueCode();
        $jobTitle = $jobTitleModel::findOrFail($id);
        return Inertia::render('Roles/Form/pageForm', compact('uniqCode', 'jobTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobTitleModel $jobTitleModel, string $id)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $this->validationText($request->all(), $id);
        $roles = $jobTitleModel::findOrFail($id);
        $roles->created_by = auth()->id();
        $roles->title = $request->input('title');
        $roles->slug = Str::slug($request->input('title'));
        $roles->title_alias = $request->input('title_alias');
        $roles->description = $request->input('description');
        $roles->update();
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $roles->title . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobTitleModel $jobTitleModel, string $id)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $roles = $jobTitleModel::findOrFail($id);
        $roles->delete();
        return redirect()->route('job_title')->with('message', 'Jabatan ' . $roles->title . ' berhasil dihapus.');
    }
    public function destroy_all(Request $request)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');

        $job = JobTitleModel::whereIn('job_title_id', $all_id)->get();
        Role::whereIn('id', $job->pluck('role_id'))->delete();
        JobTitleModel::destroy($all_id);
        return redirect()->route('job_title')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }
}
