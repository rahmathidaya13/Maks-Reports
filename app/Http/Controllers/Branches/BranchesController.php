<?php

namespace App\Http\Controllers\Branches;

use App\Traits\BranchValidation;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use Illuminate\Validation\Rule;
use App\Rules\MaxQuillCharacters;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;

class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use BranchValidation;
    protected $branchesRepository;
    public function __construct(BranchRepository $branchesRepo)
    {
        $this->branchesRepository = $branchesRepo;
    }
    public function index(Request $request)
    {
        $this->authorize('view', BranchesModel::class);
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $branch = $this->branchesRepository->getCached(auth()->id(), $filters);
        return Inertia::render('Branches/Index', compact('branch', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', BranchesModel::class);
        $branchCode = BranchesModel::generateUniqueCode();
        $this->branchesRepository->clearCache(auth()->id());
        return Inertia::render('Branches/Form/pageForm', compact('branchCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', BranchesModel::class);
        $this->validationText($request->all());
        $branch = new BranchesModel();
        $branch->created_by = auth()->id();
        $branch->branch_code = BranchesModel::generateUniqueCode();
        $branch->name = $request->input('name');
        $branch->address = $request->input('address');
        $branch->status = $request->input('status');
        $branch->save();

        // Sync Phones
        $branch->syncPhones($request->input('number_phone'));
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Cabang ' . $branch->name . ' berhasil dbuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('edit', BranchesModel::class);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BranchesModel $branchesModel, string $id)
    {
        $this->authorize('edit', BranchesModel::class);
        $branch = $branchesModel::with('branchPhone')->findOrFail($id);
        $this->branchesRepository->clearCache(auth()->id());
        return Inertia::render('Branches/Form/pageForm', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BranchesModel $branchesModel, string $id)
    {
        $this->authorize('edit', BranchesModel::class);
        $this->validationText($request->all(), $id);
        $branch = $branchesModel::findOrFail($id);
        $branch->created_by = auth()->id();
        $branch->name = $request->input('name');
        $branch->address = $request->input('address');
        $branch->status = $request->input('status');
        $branch->update();

        // Sync Phones
        $branch->syncPhones($request->input('number_phone'));
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Cabang ' . $branch->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BranchesModel $branchesModel, string $id)
    {
        $this->authorize('delete', BranchesModel::class);
        $branch = $branchesModel::findOrFail($id);
        $branch->delete();
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Cabang ' . $branch->name . ' berhasil dihapus.');
    }

    public function destroy_all(Request $request)
    {
        $this->authorize('delete', BranchesModel::class);
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');
        BranchesModel::whereIn('branches_id', $all_id)->delete();
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }

    public function reset()
    {
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Data Cabang berhasil diperbarui.');
    }
}
