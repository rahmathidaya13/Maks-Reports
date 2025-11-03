<?php

namespace App\Http\Controllers\Profile;

use Inertia\Inertia;
use App\Models\ProfileModel;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use App\Http\Controllers\Controller;
use App\Models\JobTitleModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware(['auth', 'profile.uncompleted']);
    }
    public function edit(ProfileModel $profileModel)
    {
        $branches = BranchesModel::select('branches_id', 'name')->get();
        $roles = JobTitleModel::select('job_title_id', 'title')->get();
        $profile = $profileModel->with('user')->where('users_id',  auth()->user()->id)->first();
        return Inertia::render('Profile/Index', compact('profile', 'branches', 'roles'));
    }

    public function validation($request)
    {
        return Validator::make($request, [
            'roles' => 'nullable|exists:job_title,job_title_id',
            'branches' => 'nullable|exists:branches,branches_id',
            'date_of_entry' => 'required|date',
            'birthdate' => 'required|date|before:today',
            'education' => 'required|string|max:25',
            'gender' => 'required|in:male,female',
            'number_phone' => 'required|digits_between:10,13',
            'address' => 'required|string|max:250',
            'images' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'roles.required' => 'Jabatan wajib dipilih.',
            'roles.exists' => 'Jabatan yang dipilih tidak valid.',
            'branches.required' => 'Cabang wajib dipilih.',
            'branches.exists' => 'Cabang yang dipilih tidak valid.',
            'date_of_entry.required' => 'Tanggal masuk wajib diisi.',
            'date_of_entry.date' => 'Tanggal masuk tidak valid.',
            'birthdate.required' => 'Tanggal lahir wajib diisi.',
            'birthdate.date' => 'Tanggal lahir tidak valid.',
            'birthdate.before' => 'Tanggal lahir tidak boleh melebihi tanggal hari ini.',
            'education.string' => 'Pendidikan harus berupa teks.',
            'education.max' => 'Pendidikan maksimal 25 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'number_phone.required' => 'Nomor telepon wajib diisi.',
            'number_phone.digits_between' => 'Nomor telepon harus antara 10 hingga 13 digit.',
            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 250 karakter.',
            'images.required' => 'Gambar wajib dipilih.',
            'images.image' => 'File harus berupa gambar.',
            'images.mimes' => 'Gambar harus berformat JPG, JPEG, atau PNG.',
            'images.max' => 'Ukuran gambar maksimal 2MB.',
        ])->validate();
    }

    public function update(Request $request, ProfileModel $profileModel, string $id)
    {
        $this->validation($request->all());
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
        return redirect()->route('home')->with('message', 'Profil berhasil diperbarui');
    }
}
