<?php

namespace App\Traits;

use App\Rules\MaxQuillCharacters;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

trait BranchValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', new MaxQuillCharacters(500)],
            'status' => ['required', 'string', 'max:50'],


            'number_phone' => ['required', 'array', 'min:1'],
            'number_phone.*' => [
                'required',
                'string',
                'max:13',
                'regex:/^[0-9+]+$/',
                'distinct',
                'regex:/^[0-9+]+$/',
                Rule::unique('branch_phone', 'phone')
                    ->ignore($id, 'branches_id'),
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 50 karakter.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 250 karakter.',

            'number_phone.required' => 'Nomor telepon wajib diisi.',
            'number_phone.array' => 'Nomor telepon tidak valid.',
            'number_phone.min' => 'Minimal harus ada 1 nomor telepon yang diinputkan.',

            'number_phone.*.required' => 'Nomor telepon wajib diisi.',
            'number_phone.*.string' => 'Nomor telepon harus berupa teks.',
            'number_phone.*.max' => 'Nomor telepon maksimal 13 karakter.',
            'number_phone.*.regex' => 'Nomor telepon tidak valid.',
            'number_phone.*.distinct' => 'Nomor telepon tidak boleh sama.',
            'number_phone.*.unique' => 'Nomor telepon ini sudah ada.',

            'status.required' => 'Status wajib diisi.',
            'status.string' => 'Status harus berupa teks.',
            'status.max' => 'Status maksimal 50 karakter.',
        ])->validate();
    }
}
