<?php

namespace App\Http\Controllers\Transaction;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\TransactionModel;
use App\Http\Controllers\Controller;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Repositories\TransactionRepository;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $transactionRepository;
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'order_by',
            'status',
            'page',
        ]);
        $transaction = $this->transactionRepository->getCached(auth()->id(), $filters);
        return Inertia::render('Transaction/Index', [
            'transaction' => $transaction,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = CustomerModel::select('customer_id', 'customer_name')->get();
        $product = ProductModel::select('product_id', 'name')->get();
        $this->transactionRepository->clearCache(auth()->id());
        return Inertia::render('Transaction/Form/pageForm', [
            'customer' => $customer,
            'product' => $product
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id'  => 'required|exists:products,product_id',

            'price_original' => 'required|numeric|min:0',
            'price_discount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
            'payment_type' => 'required|in:payment,repayment',
            'payment_method' => 'required|in:cash,transfer,debit',
            'amount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Customer wajib dipilih.',
            'customer_id.exists' => 'Customer tidak ditemukan.',

            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',

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
        ]);

        // hitung harga final
        $priceFinal = $request['price_original'] - ($request['price_discount'] ?? 0);

        if ($priceFinal <= 0) {
            return back()->withErrors([
                'price_discount' => 'Diskon tidak boleh melebihi harga barang.'
            ]);
        }

        if ($request['payment_type'] === 'payment') {
            $dpMin = $priceFinal * 0.5;
            if ($request['amount'] < $dpMin) {
                return back()->withErrors([
                    'amount' => 'Minimal pembayaran Dana pertama adalah Rp ' . number_format($dpMin, 0, ',', '.')
                ]);
            }
        }

        $transaction = new TransactionModel();
        $transaction->created_by = auth()->id();
        $transaction->customer_id = $request['customer_id'];
        $transaction->product_id = $request['product_id'];
        $transaction->price_original = $request['price_original'];
        $transaction->price_discount = $request['price_discount'] ?? 0;
        $transaction->price_final = $priceFinal;
        $transaction->status =   $request['payment_type'] === 'repayment' ? 'repayment' : 'payment';
        $transaction->save();

        $transaction->payments()->create([
            'created_by' => auth()->id(),
            'transaction_id' => $transaction->transaction_id,
            'amount' => $request['payment_type'] === 'repayment'
                ? $priceFinal
                : $request['amount'],
            'payment_type' => $request['payment_type'] === 'repayment' ? 'repayment' : 'payment',
            'payment_method' => $request['payment_method'],
        ]);
        $this->transactionRepository->clearCache(auth()->id());
        return redirect()->route('transaction')->with('message', 'Data transaksi berhasil disimpan.');
    }

    public function show(TransactionModel $transactionModel, string $id)
    {
        $transaction = $transactionModel::with(['customer', 'product', 'payments'])->findOrFail($id);
        $this->transactionRepository->clearCache(auth()->id());
        return Inertia::render('Transaction/Form/RepaymentForm', [
            'transaction' => $transaction
        ]);
    }
    public function settle(Request $request, TransactionModel $transactionModel, string $id)
    {
        $transaction = $transactionModel::findOrFail($id);
        // 1. Cegah pelunasan ganda
        if ($transaction->status === 'repayment') {
            return back()->withErrors(['message' => 'Transaksi sudah lunas.']);
        }

        // 2. Validasi input
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,debit',
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        // 3. Hitung total yang sudah dibayar
        $totalPaid = (int) $transaction->payments()->sum('amount');
        // 4. Hitung sisa pembayaran
        $remainingPayment = $transaction->price_final - $totalPaid;

        // // 5. Validasi pembayaran
        if ($remainingPayment <= 0) {
            return back()->withErrors(['message' => 'Tidak ada sisa pembayaran.']);
        }

        // 5. Simpan pembayaran pelunasan
        $transaction->payments()->create([
            'created_by' => auth()->id(),
            'transaction_id' => $transaction->transaction_id,
            'amount' => $remainingPayment,
            'payment_type' => 'repayment',
            'payment_method' => $validated['payment_method'],
        ]);
        // 6. Update status transaksi
        $transaction->update([
            'status' => 'repayment',
        ]);

        $this->transactionRepository->clearCache(auth()->id());
        return redirect()->route('transaction')->with('message', 'Transaksi ' . $transaction->customer->customer_name . ' berhasil dilunasi.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionModel $transactionModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionModel $transactionModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionModel $transactionModel)
    {
        //
    }
}
