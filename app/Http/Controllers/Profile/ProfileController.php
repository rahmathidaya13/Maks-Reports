<?php

namespace App\Http\Controllers\Profile;

use App\Repositories\ProfileRepository;
use App\Traits\ProfileValidation;
use Inertia\Inertia;
use App\Models\ProfileModel;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use App\Http\Controllers\Controller;
use App\Models\JobTitleModel;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ProfileValidation;
    protected $profilRepository;
    public function __construct(ProfileRepository $profile)
    {
        $this->profilRepository = $profile;
    }
    public function edit()
    {
        $branches = BranchesModel::select('branches_id', 'name')->get();
        $jobTitle = JobTitleModel::select('job_title_id', 'title')->get();
        // optimalisasi cache profile
        $usersId = auth()->user()->id;
        $profile = $this->profilRepository->getCached($usersId, ['users_id' => $usersId])
            ->items()[0];
        return Inertia::render('Profile/Index', compact('profile', 'branches', 'jobTitle'));
    }
    public function update(Request $request, ProfileModel $profileModel, string $id)
    {
        $this->validationText($request->all());
        $usersId = auth()->user()->id;
        $profile = $profileModel::findOrFail($id);
        if ($request->hasFile('images')) {
            // Hapus gambar lama
            if ($profile->images && Storage::disk('public')->exists($profile->images)) {
                Storage::disk('public')->delete($profile->images);
            }
            // Simpan file baru (nama unik biar nggak tabrakan)
            $fileName = time() . '_' . $request->file('images')->getClientOriginalName();
            $path = $request->file('images')->storeAs('profile', $fileName, 'public');
            $profile->images = $path;
        } else {
            unset($profile->images);
        }
        $profile->users_id = auth()->user()->id;
        $profile->employee_id_number = $request->input('employee_id_number');
        $profile->national_id_number = $request->input('national_id_number');
        $profile->job_title_id = $request->input('jobTitle');
        $profile->branches_id = $request->input('branches');
        $profile->date_of_entry = $request->input('date_of_entry');
        $profile->employee_status = $request->input('employee_status');
        $profile->birthplace = $request->input('birthplace');
        $profile->religion = $request->input('religion');
        $profile->birthdate = $request->input('birthdate');
        $profile->entry_year = $request->input('entry_year');
        $profile->graduation_year = $request->input('graduation_year');
        $profile->education = $request->input('education');
        $profile->major = $request->input('major');
        $profile->gender = $request->input('gender');
        $profile->number_phone = $request->input('number_phone');
        $profile->address = $request->input('address');
        $profile->is_completed = true;
        $profile->update();

        auth()->user()->update([
            'name' => $request->input('name'),
        ]);
        // Clear cache
        $this->profilRepository->clearCache($usersId);
        return redirect()->route('home')->with('message', 'Profil berhasil diperbarui');
    }

    public function infoDetail(string $id)
    {
        // optimalisasi cache profile
        $detail = $this->profilRepository->getCached($id, ['users_id' => $id])
            ->items()[0];
        return Inertia::render('Profile/InfoDetail', [
            'detail' => $detail
        ]);
    }
    public function modifyAfterCompleted(ProfileModel $profileModel, string $id)
    {
        $branches = BranchesModel::select('branches_id', 'name')->get();
        $jobTitle = JobTitleModel::select('job_title_id', 'title')->get();
        $profile = $profileModel::with(['user', 'branch', 'jobTitle'])
            ->where('users_id', $id)
            ->first();
        $this->profilRepository->clearCache($id);
        return Inertia::render('Profile/ModifyAfterCompleted', [
            'profile' => $profile,
            'branches' => $branches,
            'jobTitle' => $jobTitle
        ]);
    }
    public function modifyAfterUpdate(Request $request, ProfileModel $profileModel, string $id)
    {
        $this->validationText($request->all(), $id);
        $isDeveloper = auth()->user()->hasRole(['developer', 'admin']);
        $profile = $profileModel::with(['user', 'branch', 'jobTitle'])
            ->where('users_id', $id)
            ->first();

        // VALIDASI OTORISASI PINDAH CABANG
        $newBranchId = $request->input('branches');
        // Cek apakah user mengganti cabangnya
        if (!$isDeveloper) {
            if ($newBranchId && $newBranchId != $profile->branches_id) {
                $inputCode = $request->input('branch_code');
                // Cari data cabang yang baru di database
                $targetBranch = BranchesModel::find($newBranchId);
                // Cek apakah cabang ditemukan DAN kodenya cocok
                if (!$targetBranch || $targetBranch->branch_code !== $inputCode) {
                    return back()->withErrors([
                        'branch_code' => 'Kode Otorisasi tidak sesuai dengan kode cabang yang dipilih.',
                    ])->withInput(); // withInput mengembalikan isian form user agar tidak hilang
                }
            }
        }

        // cek apakah ada gambar baru atau tidak
        if ($request->hasFile('images')) {
            // Hapus gambar lama
            if ($profile->images && Storage::disk('public')->exists($profile->images)) {
                Storage::disk('public')->delete($profile->images);
            }
            // Simpan file baru (nama unik biar nggak tabrakan)
            $fileName = time() . '_' . $request->file('images')->getClientOriginalName();
            $path = $request->file('images')->storeAs('profile', $fileName, 'public');
            $profile->images = $path;
        } else {
            unset($profile->images);
        }

        $profile->users_id = auth()->user()->id;
        $profile->employee_id_number = $request->input('employee_id_number');
        $profile->national_id_number = $request->input('national_id_number');
        $profile->job_title_id = $request->input('jobTitle');
        $profile->branches_id = $newBranchId;
        $profile->date_of_entry = $request->input('date_of_entry');
        $profile->employee_status = $request->input('employee_status');
        $profile->birthplace = $request->input('birthplace');
        $profile->religion = $request->input('religion');
        $profile->birthdate = $request->input('birthdate');
        $profile->entry_year = $request->input('entry_year');
        $profile->graduation_year = $request->input('graduation_year');
        $profile->education = $request->input('education');
        $profile->major = $request->input('major');
        $profile->gender = $request->input('gender');
        $profile->number_phone = $request->input('number_phone');
        $profile->address = $request->input('address');
        $profile->update();
        auth()->user()->update([
            'name' => $request->input('name'),
        ]);
        $this->profilRepository->clearCache($id);
        return redirect()->route('profile.detail', $id)->with('message', 'Profil berhasil diperbarui');
    }
}
