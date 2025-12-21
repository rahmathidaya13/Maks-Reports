<?php

namespace App\Http\Controllers\Transaction;

use App\Traits\TransactionValidation;
use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\CustomerModel;
use Illuminate\Support\Carbon;
use App\Models\TransactionModel;
use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use TransactionValidation;
    protected $transactionRepository;
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }
    public function index(Request $request)
    {
        $this->authorize('view', TransactionModel::class);
        $filters = $request->only([
            'keyword',
            'limit',
            'order_by',
            'status',
            'page',
            'date_filter'
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
        $this->authorize('create', TransactionModel::class);
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
        $this->authorize('create', TransactionModel::class);
        $this->validationText($request->all());
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
        $transaction->invoice = $request['invoice'];
        $transaction->transaction_date = now();
        $transaction->customer_id = $request['customer_id'];
        $transaction->product_id = $request['product_id'];
        $transaction->price_original = $request['price_original'];
        $transaction->price_discount = $request['price_discount'] ?? 0;
        $transaction->price_final = $priceFinal;
        $transaction->status =   $request['payment_type'] === 'repayment' ? 'repayment' : 'payment';
        $transaction->save();

        $transaction->payments()->create([
            'created_by' => auth()->id(),
            'payment_date' => now(),
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
        $this->authorize('edit', TransactionModel::class);
        $transaction = $transactionModel::with(['customer', 'product', 'payments'])->findOrFail($id);
        $this->transactionRepository->clearCache(auth()->id());
        return Inertia::render('Transaction/Form/RepaymentForm', [
            'transaction' => $transaction
        ]);
    }
    public function settle(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
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
            'payment_date' => now(),
            'transaction_id' => $transaction->transaction_id,
            'amount' => $remainingPayment,
            'payment_type' => 'repayment',
            'payment_method' => $validated['payment_method'],
        ]);
        // 6. Update status transaksi
        $transaction->update([
            'status' => 'repayment',
        ]);
        $message = 'Transaksi tanggal ' . Carbon::parse($transaction->transaction_date)->format('d/m/Y') . ' pelanggan ' . $transaction->customer->customer_name . ' berhasil dilunasi.';
        $this->transactionRepository->clearCache(auth()->id());
        return redirect()->route('transaction')->with('message', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);

        $customer = CustomerModel::select('customer_id', 'customer_name')->get();
        $product = ProductModel::select('product_id', 'name')->get();

        $transaction = $transactionModel::with([
            'creator:id,name',
            'customer:customer_id,customer_name,number_phone_customer',
            'product:product_id,name',
            'payments:payment_id,transaction_id,payment_date,payment_type,payment_method,amount'
        ])->findOrFail($id);

        if ($transaction->status === 'repayment') {
            return redirect()
                ->route('transaction')
                ->with('message', 'Transaksi ini sudah lunas tidak bisa diubah.');
        }

        $this->transactionRepository->clearCache(auth()->id());
        return Inertia::render('Transaction/Form/pageForm', [
            'transaction' => $transaction,
            'customer' => $customer,
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
        $this->validationText($request->all(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionModel $transactionModel)
    {
        $this->authorize('delete', TransactionModel::class);
    }
}
