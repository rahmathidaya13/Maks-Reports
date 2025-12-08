<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ProfileValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/u',
            'employee_id_number' => 'required|string|unique:profile,employee_id_number,' . $id . ',users_id',
            'national_id_number' => 'required|string|unique:profile,national_id_number,' . $id . ',users_id',
            'employee_status' => 'required|in:contract,permanent,intern,freelance',
            'jobTitle' => 'required|exists:job_title,job_title_id',
            'branches' => 'required|exists:branches,branches_id',
            'date_of_entry' => 'required|date',
            'birthdate' => 'required|date|before:today',
            'entry_year' => 'required|string|max:4',
            'graduation_year' => 'required|string|max:4',
            'education' => 'required|string|max:50',
            'major' => 'required|string|max:100',
            'birthplace' => 'required|string|max:100',
            'religion' => 'required|in:islam,kristen,katolik,hindu,budha,konghucu',
            'gender' => 'required|in:male,female',
            'number_phone' => 'required|digits_between:10,13|unique:profile,number_phone,' . $id . ',users_id',
            'address' => 'required|string|max:250',
            'images' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 50 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',

            'employee_status.required' => 'Status karyawan wajib dipilih.',
            'employee_status.in' => 'Status karyawan tidak valid.',

            'employee_id_number.required' => 'ID Karyawan wajib diisi.',
            'employee_id_number.string' => 'ID Karyawan tidak valid.',
            'employee_id_number.unique' => 'ID Karyawan sudah terdaftar.',

            'national_id_number.required' => 'NIK KTP wajib diisi.',
            'national_id_number.string' => 'NIK KTP tidak valid.',
            'national_id_number.unique' => 'NIK KTP sudah terdaftar.',

            'jobTitle.required' => 'Jabatan wajib dipilih.',
            'jobTitle.exists' => 'Jabatan yang dipilih tidak valid.',

            'branches.required' => 'Lokasi wajib dipilih.',
            'branches.exists' => 'Lokasi yang dipilih tidak valid.',

            'date_of_entry.required' => 'Tanggal masuk wajib diisi.',
            'date_of_entry.date' => 'Tanggal masuk tidak valid.',

            'birthdate.required' => 'Tanggal lahir wajib diisi.',
            'birthdate.date' => 'Tanggal lahir tidak valid.',
            'birthdate.before' => 'Tanggal lahir tidak boleh melebihi tanggal hari ini.',

            'entry_year.required' => 'Tahun masuk wajib diisi.',
            'entry_year.string' => 'Tahun masuk harus berupa angka.',
            'entry_year.max' => 'Tahun masuk maksimal 4 karakter.',

            'graduation_year.required' => 'Tahun lulus wajib diisi.',
            'graduation_year.string' => 'Tahun lulus harus berupa angka.',
            'graduation_year.max' => 'Tahun lulus maksimal 4 karakter.',

            'major.required' => 'Jurusan wajib diisi.',
            'major.string' => 'Jurusan harus berupa teks.',
            'major.max' => 'Jurusan maksimal 100 karakter.',

            'birthplace.required' => 'Tempat lahir wajib diisi.',
            'birthplace.string' => 'Tempat lahir harus berupa teks.',
            'birthplace.max' => 'Tempat lahir maksimal 100 karakter.',

            'religion.required' => 'Agama wajib dipilih.',
            'religion.in' => 'Agama tidak valid.',

            'education.required' => 'Pendidikan wajib diisi.',
            'education.string' => 'Pendidikan harus berupa teks.',
            'education.max' => 'Pendidikan maksimal 50 karakter.',

            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',

            'number_phone.required' => 'Nomor telepon wajib diisi.',
            'number_phone.digits_between' => 'Nomor telepon harus antara 10 hingga 13 digit.',
            'number_phone.unique' => 'Nomor telepon sudah terdaftar.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 250 karakter.',

            'images.image' => 'File harus berupa gambar.',
            'images.mimes' => 'Gambar harus berformat JPG, JPEG, atau PNG.',
            'images.max' => 'Ukuran gambar maksimal 2MB.',
        ])->validate();
    }
}
