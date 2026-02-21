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
        $branch->status_official = $request->input('status_official');
        $branch->save();

        foreach ($request['phones'] as $phoneData) {
            $branch->branchPhone()->create([
                'phone' => $phoneData['phone'],
            ]);
        }

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
        $branch->status_official = $request->input('status_official');
        $branch->update();


        $requestPhone = collect($request['phones'])->pluck('phone_id')->filter()->toArray();

        $branch->branchPhone()
            ->whereNotIn('branch_phone_id', $requestPhone)
            ->delete();

        foreach ($request['phones'] as $phoneData) {
            $branch->branchPhone()->updateOrCreate(
                ['branch_phone_id' => $phoneData['phone_id']], // Kunci update (jika null, dia create)
                ['phone' => $phoneData['phone']]
            );
        }
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

        if ($branch->profile()->exists()) {
            // Redirect kembali dengan pesan Error
            return redirect()->back()->with('warning', 'Cabang ' . $branch->name . ' ini sedang digunakan oleh user, menghapus dapat membuat kesalahan. Cabang ' . $branch->name . ' ini hanya dapat diubah.');
        }

        $branch->delete();
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Cabang ' . $branch->name . ' berhasil dihapus.');
    }

    public function destroy_all(Request $request)
    {
        $this->authorize('delete', BranchesModel::class);
        $all_id = $request->input('all_id', []);

        // Validasi jika tidak ada yang dipilih
        if (empty($all_id)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        // A. Cari ID Cabang yang statusnya 'OFFICIAL' (Pusat)
        // Cabang pusat HARAM dihapus karena akan merusak logika sistem.
        $officialIds = BranchesModel::whereIn('branches_id', $all_id)
            ->where('status_official', 'official')
            ->pluck('branches_id')
            ->toArray();

        // B. Cari ID Cabang yang SEDANG DIGUNAKAN (Relasi)
        // Cek apakah cabang punya Karyawan (users) atau Harga Produk (prices)
        // Pastikan nama relasi 'profile' sesuai dengan BranchesModel Anda.
        $usedIds = BranchesModel::whereIn('branches_id', $all_id)
            ->where(function ($query) {
                $query->has('profile.user');
            })
            ->pluck('branches_id')
            ->toArray();

        // Gabungkan semua ID yang TIDAK BOLEH dihapus (Official + Terpakai)
        $protectedIds = array_unique(array_merge($officialIds, $usedIds));

        // Hitung ID yang AMAN untuk dihapus (Selisih antara Semua Pilihan - Yang Dilindungi)
        $idsToDelete = array_diff($all_id, $protectedIds);

        // Hitung statistik untuk pesan notifikasi
        $countDeleted = count($idsToDelete);
        $countSkipped = count($protectedIds);

        if ($countDeleted > 0) {
            // Hapus data cabang yang aman
            BranchesModel::whereIn('branches_id', $idsToDelete)->delete();

            // Hapus cache (Penting agar dropdown cabang di tempat lain terupdate)
            $this->branchesRepository->clearCache(auth()->id());
        }

        // Skenario 1: Gagal Total (Semua yang dipilih ternyata Official atau Terpakai)
        if ($countDeleted === 0 && $countSkipped > 0) {
            return back()->with('warning', 'Gagal menghapus. Cabang yang dipilih sedang memiliki data aktif atau sedang digunakan.');
        }

        // Skenario 2: Berhasil Sebagian (Partial Success)
        // Contoh: Pilih 5, tapi 1 Pusat (dilewati), 4 Cabang Biasa (dihapus).
        if ($countDeleted > 0 && $countSkipped > 0) {
            return redirect()->route('branch')->with(
                'warning',
                "$countDeleted cabang berhasil dihapus. $countSkipped cabang DILEWATI karena berstatus Official atau sedang digunakan."
            );
        }

        return redirect()->route('branch')->with('message', "$countDeleted Cabang berhasil dihapus.");
    }

    public function reset()
    {
        $this->branchesRepository->clearCache(auth()->id());
        return redirect()->route('branch')->with('message', 'Data Cabang berhasil diperbarui.');
    }
}
