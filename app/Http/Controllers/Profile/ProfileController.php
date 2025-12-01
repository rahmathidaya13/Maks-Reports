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

    public function __construct()
    {
        $this->middleware(['auth', 'profile.uncompleted']);
    }
    public function edit(ProfileModel $profileModel)
    {
        $branches = BranchesModel::select('branches_id', 'name')->get();
        $roles = JobTitleModel::select('job_title_id', 'title')->get();
        $profile = $profileModel->with('user')->where('users_id', auth()->user()->id)->first();
        return Inertia::render('Profile/Index', compact('profile', 'branches', 'roles'));
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
        $profile->job_title_id = $request->input('roles');
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
}
