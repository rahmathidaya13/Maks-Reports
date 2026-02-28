<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait TransactionValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'invoice' => 'required|max:25',
            'customer_id' => 'required',
            'items' => 'required|array|min:1', // Wajib array dan minimal 1
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_original' => 'required|numeric|min:0', // Harga Manual
            'items.*.discount_percentage' => 'nullable|numeric|min:0', // Diskon Manual

            'payment_type' => 'required|in:payment,repayment',
            'payment_method' => 'required|in:cash,transfer,debit,qris',
            'amount' => 'required_if:payment_type,payment|nullable|numeric|min:0',

            'tax_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'customer_id.required' => 'Pelanggan harus dipilih.',
            'invoice.required' => 'Nomor invoice harus diisi.',
            'invoice.max' => 'Nomor invoice tidak boleh lebih dari 25 karakter.',

            'items.required' => 'Produk harus diisi.',
            'items.array' => 'Produk tidak valid.',
            'items.min' => 'Minimal satu Produk harus dipilih.',

            'items.*.product_id.required' => 'Produk harus dipilih.',
            'items.*.quantity.required' => 'Jumlah Produk harus diisi.',
            'items.*.quantity.integer' => 'Jumlah Produk harus berupa angka.',
            'items.*.quantity.min' => 'Jumlah Produk minimal 1.',
            'items.*.price_original.required' => 'Harga Produk harus diisi.',
            'items.*.price_original.numeric' => 'Harga Produk harus berupa angka.',
            'items.*.price_original.min' => 'Harga Produk tidak boleh kurang dari 0.',
            'items.*.discount_percentage.numeric' => 'Diskon harus berupa angka.',
            'items.*.discount_percentage.min' => 'Diskon tidak boleh kurang dari 0.',
            'payment_type.required' => 'Tipe pembayaran harus dipilih.',
            'payment_type.in' => 'Tipe pembayaran tidak valid.',
            'payment_method.required' => 'Metode pembayaran harus dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',

            'amount.required_if' => 'Nominal DP harus diisi ketika tipe pembayaran adalah DP.',
            'amount.numeric' => 'Nominal DP harus berupa angka.',
            'amount.min' => 'Nominal DP tidak boleh kurang dari 0.',

            'tax_percentage.numeric' => 'Persentase pajak harus berupa angka.',
            'tax_percentage.min' => 'Persentase pajak tidak boleh kurang dari 0.',
            'tax_percentage.max' => 'Persentase pajak tidak boleh lebih dari 100.',
        ])->validate();
    }
}
