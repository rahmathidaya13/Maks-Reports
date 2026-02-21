<?php

namespace App\Traits;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

trait BranchValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:250'],
            'status' => ['required', 'string', 'max:50'],
            'status_official' => ['required', 'string', 'max:50', 'in:official,unofficial'],


            'phones' => ['required', 'array', 'min:1'],
            'phones.*.phone' => [
                'required',
                'numeric',
                'digits_between:10,13',
                'distinct',
                Rule::unique('branch_phone', 'phone')
                    ->ignore($id, 'branches_id'),
            ],
        ], [
            'name.required' => 'Nama Cabang wajib diisi.',
            'name.string' => 'Nama Cabang harus berupa teks.',
            'name.max' => 'Nama Cabang maksimal 50 karakter.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 250 karakter.',

            'status.required' => 'Status Operasional wajib dipilih.',
            'status.string' => 'Status Operasional harus berupa teks.',
            'status.max' => 'Status Operasional maksimal 50 karakter.',

            'status_official.required' => 'Status official wajib dipilih.',
            'status_official.string' => 'Status official harus berupa teks.',
            'status_official.max' => 'Status official maksimal 50 karakter.',
            'status_official.in' => 'Status official tidak sesuai.',

            'phones.required' => 'Wajib ada minimal satu nomor telepon.',
            'phones.min'      => 'Minimal harus ada 1 nomor telepon.',

            'phones.*.phone.required' => 'Kolom nomor telepon tidak boleh kosong.',
            'phones.*.phone.numeric'  => 'Nomor telepon harus berupa angka.',
            'phones.*.phone.digits_between' => 'Nomor telepon harus antara 10-13 digit.',
            'phones.*.phone.distinct' => 'Ada nomor telepon yang sama di dalam form ini.',
            'phones.*.phone.unique'   => 'Nomor telepon ini sudah terdaftar di cabang lain.',

        ])->validate();
    }
}
