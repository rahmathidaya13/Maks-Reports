<?php

namespace App\Traits;

use App\Rules\MaxQuillCharacters;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

trait JobTitleValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'title' => [
                'required',
                'string',
                'max:50',
                Rule::unique('job_title', 'title')
                    ->ignore($id, 'job_title_id'),
            ],
            'title_alias' => [
                'required',
                'string',
                'max:50',
                Rule::unique('job_title', 'title_alias')
                    ->ignore($id, 'job_title_id'),
            ],
            'description' => ['required', 'string', new MaxQuillCharacters(500)],
        ], [
            'title.required' => 'Nama jabatan tidak boleh kosong.',
            'title.string' => 'Nama jabatan harus berupa teks.',
            'title.max' => 'Nama jabatan maksimal 50 karakter.',
            'title.unique' => 'Nama jabatan sudah terdaftar.',

            'title_alias.required' => ' Singkatan jabatan tidak boleh kosong.',
            'title_alias.string' => ' Singkatan jabatan harus berupa teks.',
            'title_alias.max' => ' Singkatan jabatan maksimal 15 karakter.',
            'title_alias.unique' => ' Singkatan jabatan sudah digunakan.',

            'description.required' => 'Deskripsi tidak boleh kosong.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',

        ])->validate();
    }
}
