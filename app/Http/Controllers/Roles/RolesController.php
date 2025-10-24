<?php

namespace App\Http\Controllers\Roles;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        return Inertia::render('Roles/Index', compact('roles', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Roles/Form/pageForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $roles = new RolesModel();
        $roles->name = $request->input('name');
        $roles->description = $request->input('description');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->rolesRepos->clearCache(auth()->id());
        $roles = RolesModel::findOrFail($id);
        $roles->name = $request->input('name');
        $roles->description = $request->input('description');
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
