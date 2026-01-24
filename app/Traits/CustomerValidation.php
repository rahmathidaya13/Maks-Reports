<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait CustomerValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'national_id' => ['nullable', 'string', 'max:20', 'unique:customers,national_id_number,' . $id . ',customer_id'],
            'customer_name' => ['required', 'string', 'max:50'],
            'number_phone' => ['required', 'string', 'max:13', 'regex:/^[0-9+]+$/', 'unique:customers,number_phone_customer,' . $id . ',customer_id'],
            'city' => ['required', 'string', 'max:50'],
            'province' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:250'],
            'type_bussiness' => ['required', 'string', 'max:255'],

        ], [
            'national_id.string' => 'Nomor KTP harus berupa teks.',
            'national_id.max' => 'Nomor KTP maksimal 20 karakter.',
            'national_id.unique' => 'Nomor KTP sudah terdaftar.',


            'customer_name.required' => 'Nama wajib diisi.',
            'customer_name.string' => 'Nama harus berupa teks.',
            'customer_name.max' => 'Nama maksimal 50 karakter.',

            'number_phone.required' => 'Nomor Telepon wajib diisi.',
            'number_phone.string' => 'Nomor Telepon harus berupa teks.',
            'number_phone.max' => 'Nomor Telepon maksimal 13 karakter.',
            'number_phone.regex' => 'Nomor Telepon tidak valid.',
            'number_phone.unique' => 'Nomor Telepon sudah terdaftar.',

            'city.required' => 'Kota wajib diisi.',
            'city.string' => 'Kota harus berupa teks.',
            'city.max' => 'Kota maksimal 50 karakter.',

            'province.required' => 'Provinsi wajib diisi.',
            'province.string' => 'Provinsi harus berupa teks.',
            'province.max' => 'Provinsi maksimal 50 karakter.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 250 karakter.',

            'type_bussiness.required' => 'Jenis Usaha wajib diisi.',
            'type_bussiness.string' => 'Jenis Usaha harus berupa teks.',
            'type_bussiness.max' => 'Jenis Usaha maksimal 255 karakter.',
        ])->validate();
    }
}
