<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ProductValidation
{
    public function validationText($request, $id = null)
    {
        $validated = Validator::make($request, [

            'name' => ['required', 'string', 'max:100'],
            'item_condition' => 'required|in:new,used,refurbished,damaged,discontinued|string',
            'link' => 'nullable|url|max:500|unique:products,link,' . $id . ',product_id|regex:/^https?:\/\//i',
            'category' => 'required|string|max:100',
            'image' => [$id ? 'nullable' : 'required', 'image', 'max:2048', 'mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/svg'],

            'branch_prices' => 'required|array|min:1',
            'branch_prices.*.branch_id' => 'required|exists:branches,branches_id|distinct',
            'branch_prices.*.base_price' => 'required|numeric|min:10',
            'branch_prices.*.discount_price' => 'nullable|numeric|min:0|lt:branch_prices.*.base_price',
            'branch_prices.*.valid_from' => 'nullable|date|before_or_equal:branch_prices.*.valid_until|date_format:Y-m-d',
            'branch_prices.*.valid_until' => 'nullable|date|after_or_equal:branch_prices.*.valid_from|date_format:Y-m-d',
            'branch_prices.*.status' => 'required|in:draft,published|string',
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

            'image.required' => 'Silakan unggah gambar produk.',
            'image.image' => 'Gambar harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'image.mimetypes' => 'Format gambar tidak didukung. Hanya menerima JPEG, PNG, JPG, WEBP, SVG.',

            'branch_prices.required' => 'Salah satu cabang wajib dipilih.',
            'branch_prices.array' => 'Salah satu cabang wajib dipilih.',
            'branch_prices.min' => 'Pilih minimal satu cabang.',

            'branch_prices.*.branch_id.required' => 'Cabang wajib dipilih.',
            'branch_prices.*.branch_id.exists' => 'Cabang tidak ditemukan.',
            'branch_prices.*.branch_id.distinct' => 'Pilih cabang yang berbeda.',

            'branch_prices.*.base_price.required' => 'Harga produk wajib diisi.',
            'branch_prices.*.base_price.numeric' => 'Harga produk harus berupa angka.',
            'branch_prices.*.base_price.min' => 'Harga produk minimal adalah Rp 10.',

            'branch_prices.*.discount_price.numeric' => 'Harga diskon harus berupa angka.',
            'branch_prices.*.discount_price.min' => 'Harga diskon minimal adalah Rp 0.',
            'branch_prices.*.discount_price.lt' => 'Harga diskon harus lebih kecil dari harga produk.',

            'branch_prices.*.valid_from.date' => 'Tanggal berlaku harus berupa tanggal yang valid.',
            'branch_prices.*.valid_from.before_or_equal' => 'Tanggal berlaku harus sebelum atau sama dengan tanggal berakhir.',
            'branch_prices.*.valid_from.date_format' => 'Format tanggal berlaku tidak valid.',

            'branch_prices.*.valid_until.date' => 'Tanggal berakhir harus berupa tanggal yang valid.',
            'branch_prices.*.valid_until.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal berlaku.',
            'branch_prices.*.valid_until.date_format' => 'Format tanggal berakhir tidak valid.',

            'branch_prices.*.status.required' => 'Status Publikasi wajib dipilih.',
            'branch_prices.*.status.in' => 'Status Publikasi tidak didukung.',
            'branch_prices.*.status.string' => 'Status Publikasi harus berupa teks.',
        ])->validate();

        return $validated;
    }
}
