<?php

namespace App\Http\Controllers\Profile;

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
    public function edit(ProfileModel $profileModel)
    {
        $branches = BranchesModel::select('branches_id', 'name')->get();
        $JobTitle = JobTitleModel::select('job_title_id', 'title')->get();
        $profile = $profileModel::with('user')->where('users_id', auth()->user()->id)->first();
        return Inertia::render('Profile/Index', compact('profile', 'branches', 'JobTitle'));
    }
    public function update(Request $request, ProfileModel $profileModel, string $id)
    {
        $this->validationText($request->all());
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
        $profile->id_number_employee = $request->input('id_number');
        $profile->job_title_id = $request->input('jobTitle');
        $profile->branches_id = $request->input('branches');
        $profile->date_of_entry = $request->input('date_of_entry');
        $profile->birthdate = $request->input('birthdate');
        $profile->education = $request->input('education');
        $profile->gender = $request->input('gender');
        $profile->number_phone = $request->input('number_phone');
        $profile->address = $request->input('address');
        $profile->is_completed = true;
        $profile->update();

        auth()->user()->update([
            'name' => $request->input('name'),
        ]);
        return redirect()->route('home')->with('message', 'Profil berhasil diperbarui');
    }

    public function infoDetail(ProfileModel $profileModel, string $id)
    {
        $detail = $profileModel::with(['user', 'branch', 'jobTitle'])
            ->where('users_id', $id)
            ->first();
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
        return Inertia::render('Profile/ModifyAfterCompleted', [
            'profile' => $profile,
            'branches' => $branches,
            'jobTitle' => $jobTitle
        ]);
    }
    public function modifyAfterUpdate(Request $request, ProfileModel $profileModel, string $id)
    {
        $this->validationText($request->all(), $id);
        $profile = $profileModel::with(['user', 'branch', 'jobTitle'])
            ->where('users_id', $id)
            ->first();

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
        $profile->id_number_employee = $request->input('id_number');
        $profile->job_title_id = $request->input('jobTitle');
        $profile->branches_id = $request->input('branches');
        $profile->date_of_entry = $request->input('date_of_entry');
        $profile->birthdate = $request->input('birthdate');
        $profile->education = $request->input('education');
        $profile->gender = $request->input('gender');
        $profile->number_phone = $request->input('number_phone');
        $profile->address = $request->input('address');
        $profile->update();

        auth()->user()->update([
            'name' => $request->input('name'),
        ]);
        return redirect()->route('profile.detail', $id)->with('message', 'Profil berhasil diperbarui');
    }
}
