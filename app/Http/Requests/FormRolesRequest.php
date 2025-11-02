<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = ['id'];
        return [
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'short_name' => ['required', 'string', 'max:50', 'unique:roles,short_name' . $id . 'roles_id'],
            'description' => ['required', 'string', 'max:255'],

            'can_view' => ['boolean'],
            'can_add' => ['boolean'],
            'can_edit' => ['boolean'],
            'can_delete' => ['boolean'],
            'can_export' => ['boolean'],
            'can_import' => ['boolean'],
            'can_share' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.string' => 'Nama jabatan harus berupa teks.',
            'name.max' => 'Nama jabatan maksimal 100 karakter.',
            'name.unique' => 'Nama jabatan sudah terdaftar.',

            'short_name.required' => 'Nama singkat harus diisi.',
            'short_name.string' => 'Nama singkat harus berupa teks.',
            'short_name.max' => 'Nama singkat maksimal 50 karakter.',
            'short_name.unique' => 'Nama singkat sudah digunakan.',

            'description.required' => 'Deskripsi harus diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',

            'can_view.boolean' => 'Nilai akses lihat harus berupa true atau false.',
            'can_add.boolean' => 'Nilai akses tambah harus berupa true atau false.',
            'can_edit.boolean' => 'Nilai akses ubah harus berupa true atau false.',
            'can_delete.boolean' => 'Nilai akses hapus harus berupa true atau false.',
            'can_export.boolean' => 'Nilai akses ekspor harus berupa true atau false.',
            'can_import.boolean' => 'Nilai akses impor harus berupa true atau false.',
            'can_share.boolean' => 'Nilai akses berbagi harus berupa true atau false.',
        ];
    }
}
