<?php

namespace App\Http\Controllers\Roles;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\FormRolesRequest;
use App\Models\RolesModel;
use App\Repositories\RolesRepository;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        $roles = $this->rolesRepos->getAllByUser(auth()->id(), $filters);
        // $roles = RolesModel::paginate(10);
        return Inertia::render('Roles/Index', compact('roles', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $uniqCode = RolesModel::generateUniqueCode();
        return Inertia::render('Roles/Form/pageForm', compact('uniqCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormRolesRequest $request)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $request->validated();
        $roles = new RolesModel();
        $roles->created_by = auth()->id();
        $roles->position_code = RolesModel::generateUniqueCode();
        $roles->short_name = $request['short_name'];
        $roles->name = $request['name'];
        $roles->description = $request['description'];
        $roles->can_view = $request['view'];
        $roles->can_add = $request['add'];
        $roles->can_edit = $request['edit'];
        $roles->can_delete = $request['delete'];
        $roles->can_export = $request['export'];
        $roles->can_import = $request['import'];
        $roles->can_share = $request['share'];
        $roles->save();
        return redirect()->route('roles')->with('message', 'Jabatan ' . $roles->name . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $uniqCode = RolesModel::generateUniqueCode();
        $roles = RolesModel::findOrFail($id);
        return Inertia::render('Roles/Form/pageForm', compact('uniqCode', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormRolesRequest $request, string $id)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $request['id'] = $id;
        $request->validated();
        $roles = RolesModel::findOrFail($id);
        $roles->created_by = auth()->id();
        $roles->position_code = RolesModel::generateUniqueCode();
        $roles->short_name = $request['short_name'];
        $roles->name = $request['name'];
        $roles->description = $request['description'];
        $roles->can_view = $request['view'];
        $roles->can_add = $request['add'];
        $roles->can_edit = $request['edit'];
        $roles->can_delete = $request['delete'];
        $roles->can_export = $request['export'];
        $roles->can_import = $request['import'];
        $roles->can_share = $request['share'];
        $roles->update();
        return redirect()->route('roles')->with('message', 'Jabatan ' . $roles->name . ' berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
