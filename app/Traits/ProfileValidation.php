<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ProfileValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/u',
            'id_number' => 'required|string|unique:profile,id_number_employee,' . $id . ',users_id',
            'jobTitle' => 'required|exists:job_title,job_title_id',
            'branches' => 'required|exists:branches,branches_id',
            'date_of_entry' => 'required|date',
            'birthdate' => 'required|date|before:today',
            'education' => 'required|string|max:25',
            'gender' => 'required|in:male,female',
            'number_phone' => 'required|digits_between:10,13|unique:profile,number_phone,' . $id . ',users_id',
            'address' => 'required|string|max:250',
            'images' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 50 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'id_number.required' => 'ID Karyawan wajib diisi.',
            'id_number.string' => 'ID Karyawan tidak valid.',
            'id_number.unique' => 'ID Karyawan sudah terdaftar.',
            'jobTitle.required' => 'Jabatan wajib dipilih.',
            'jobTitle.exists' => 'Jabatan yang dipilih tidak valid.',
            'branches.required' => 'Lokasi wajib dipilih.',
            'branches.exists' => 'Lokasi yang dipilih tidak valid.',
            'date_of_entry.required' => 'Tanggal masuk wajib diisi.',
            'date_of_entry.date' => 'Tanggal masuk tidak valid.',
            'birthdate.required' => 'Tanggal lahir wajib diisi.',
            'birthdate.date' => 'Tanggal lahir tidak valid.',
            'birthdate.before' => 'Tanggal lahir tidak boleh melebihi tanggal hari ini.',
            'education.required' => 'Pendidikan wajib diisi.',
            'education.string' => 'Pendidikan harus berupa teks.',
            'education.max' => 'Pendidikan maksimal 25 karakter.',
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
