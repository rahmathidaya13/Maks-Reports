<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait TransactionValidation
{
    public function validationText($request, $id = null)
    {
        return Validator::make($request, [
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id'  => 'required|exists:products,product_id',

            'invoice' => 'required|max:25|unique:transactions,invoice,' . $id . ',transaction_id',

            'price_original' => 'required|numeric|min:0',
            'price_discount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
            'payment_type' => 'required|in:payment,repayment',
            'payment_method' => 'required|in:cash,transfer,debit,qris',
            'amount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Customer wajib dipilih.',
            'customer_id.exists' => 'Customer tidak ditemukan.',

            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',

            'invoice.required' => 'Nomor faktur wajib diisi.',
            'invoice.max' => 'Nomor faktur tidak boleh lebih dari 25 karakter.',
            'invoice.unique' => 'Nomor faktur sudah digunakan.',

            'price_original.required' => 'Harga barang wajib diisi.',
            'price_original.numeric' => 'Harga barang harus berupa angka.',

            'price_discount.required_if' => 'Diskon wajib diisi.',
            'price_discount.numeric' => 'Diskon harus berupa angka.',
            'price_discount.min' => 'Diskon minimal Rp 0.',

            'payment_type.required' => 'Jenis pembayaran wajib dipilih.',
            'payment_type.in' => 'Jenis pembayaran tidak valid.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',

            'amount.required_if' => 'Nominal DP wajib diisi.',
            'amount.numeric' => 'Nominal DP harus berupa angka.',
        ])->validate();
    }
}
