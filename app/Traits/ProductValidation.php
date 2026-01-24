<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ProductValidation
{
    public function validationText($request, $id = null)
    {
        $validated = Validator::make($request, [

            'name' => 'required|string|max:100|unique:products,name,' . $id . ',product_id',
            'link' => 'nullable|url|max:500|unique:products,link,' . $id . ',product_id|regex:/^https?:\/\//i',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,published|string',
            'image' => [$id ? 'nullable' : 'required', 'image', 'max:2048', 'mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/svg'],

            'item_condition' => 'required|in:new,used,refurbished,damaged,discontinued|string',

            'valid_from' => 'required|date|before_or_equal:valid_until|date_format:Y-m-d',
            'valid_until' => 'nullable|date|after_or_equal:valid_from|date_format:Y-m-d',

            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:base_price',

            'branch' => 'required|array|min:1',
            'branch.*' => 'required|exists:branches,branches_id|distinct',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'name.unique' => 'Nama sudah ada dalam sistem.',

            'item_condition.required' => 'Kondisi produk wajib dipilih.',
            'item_condition.in' => 'Kondisi produk tidak sesuai dengan pilihan.',
            'item_condition.string' => 'Kondisi produk harus berupa teks yang jelas.',

            'link.url' => 'Link harus berupa URL yang valid.',
            'link.max' => 'Link maksimal 500 karakter.',
            'link.unique' => 'Link sudah ada dalam sistem.',
            'link.regex' => 'Link harus diawali dengan http:// atau https://',

            'category.required' => 'Kategori wajib dipilih.',
            'category.string' => 'Kategori harus berupa teks.',
            'category.max' => 'Kategori maksimal 100 karakter.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak didukung.',
            'status.string' => 'Status harus berupa teks.',

            'image.required' => 'Silakan unggah gambar produk.',
            'image.image' => 'Gambar harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'image.mimetypes' => 'Format gambar tidak didukung. Hanya menerima JPEG, PNG, JPG, WEBP, SVG.',


            'valid_from.required' => 'Tanggal berlaku wajib diisi.',
            'valid_from.date' => 'Tanggal berlaku harus berupa tanggal yang valid.',
            'valid_from.before_or_equal' => 'Tanggal berlaku harus sebelum atau sama dengan tanggal berakhir.',
            'valid_from.date_format' => 'Format tanggal berlaku tidak valid.',
            'valid_from.after_or_equal' => 'Tanggal berlaku harus setelah atau sama dengan hari ini.',

            'valid_until.date' => 'Tanggal berakhir harus berupa tanggal yang valid.',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal berlaku.',
            'valid_until.date_format' => 'Format tanggal berakhir tidak valid.',

            'base_price.required' => 'Harga produk wajib diisi.',
            'base_price.numeric' => 'Harga produk harus berupa angka.',
            'base_price.min' => 'Harga produk minimal adalah Rp 0.',

            'discount_price.numeric' => 'Harga diskon harus berupa angka.',
            'discount_price.min' => 'Harga diskon minimal adalah Rp 0.',
            'discount_price.lt' => 'Harga diskon harus lebih kecil dari harga produk.',

            'branch.required' => 'Cabang tersedia wajib dipilih.',
            'branch.array' => 'Cabang tersedia tidak valid.',
            'branch.min' => 'Pilih minimal satu cabang yang tersedia.',

            'branch.*.required' => 'Cabang tersedia wajib dipilih.',
            'branch.*.exists' => 'Cabang tersedia tidak ditemukan di sistem.',
            'branch.*.distinct' => 'Cabang tersedia duplikat ditemukan.',


        ])->validate();

        return $validated;
    }
}
