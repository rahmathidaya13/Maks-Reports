<?php

namespace App\Http\Controllers\Transaction;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\CustomerModel;
use Illuminate\Support\Carbon;
use App\Models\TransactionModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\TransactionValidation;
use App\Repositories\DashboardRepository;
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
        $request->validate([
            'keyword' => 'nullable|string|max:100',
            'limit' => 'nullable|in:10,20,30,50,100',
            'order_by' => 'nullable|in:desc,asc',
            'status' => 'nullable|in:payment,repayment,cancelled',
            'date_filter' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);
        $filters = $request->only([
            'keyword',
            'limit',
            'order_by',
            'status',
            'page',
            'date_filter'
        ]);
        $transaction = $this->transactionRepository->getCached(auth()->id(), $filters);
        app(DashboardRepository::class)->clearCache(auth()->id());
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

        $customer = CustomerModel::select(['customer_id', 'customer_name'])
            ->where('created_by', auth()->id())->get();

        $product = ProductModel::select('product_id', 'name')->get();
        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());
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

        // 1. Hitung Harga Final
        $priceFinal = (int) $request['price_original'] - ($request['price_discount'] ?? 0);

        // 2. Validasi Bisnis (Early Return)
        if ($priceFinal <= 0) {
            return back()->withErrors([
                'price_discount' => 'Diskon tidak boleh melebihi harga barang.'
            ]);
        }

        if ($request['payment_type'] === 'payment') {
            $dpMin = $priceFinal * 0.5;
            if ($request['amount'] < $dpMin) {
                return back()->withErrors([
                    'amount' => 'Minimal Dana Pertama (DP) adalah Rp ' . number_format($dpMin, 0, ',', '.')
                ]);
            }
            if ($request['amount'] >= $priceFinal) {
                return back()->withErrors([
                    'amount' => 'Nominal DP tidak boleh menyamai atau melebihi total harga produk. Gunakan opsi Lunas Langsung.'
                ]);
            }
        }
        try {
            // 3. Gunakan DB Transaction untuk keamanan data
            $transaction = DB::transaction(function () use ($request, $priceFinal) {
                $transaction = TransactionModel::create([
                    'created_by'       => auth()->id(),
                    'invoice'          => $request['invoice'],
                    'transaction_date' => now(),
                    'customer_id'      => $request['customer_id'],
                    'product_id'       => $request['product_id'],
                    'price_original'   => $request['price_original'],
                    'price_discount'   => $request['price_discount'] ?? 0,
                    'price_final'      => $priceFinal,
                    'status'           => $request['payment_type'] === 'repayment' ? 'repayment' : 'payment',
                ]);

                // Simpan Detail Pembayaran Pertama
                $transaction->payments()->create([
                    'created_by'     => auth()->id(),
                    'payment_date'   => now(),
                    'amount'         => $request['payment_type'] === 'repayment' ? $priceFinal : $request['amount'],
                    'payment_type'   => $request['payment_type'] === 'repayment' ? 'repayment' : 'payment',
                    'payment_method' => $request['payment_method'],
                ]);

                return $transaction->load(['creator', 'customer', 'product', 'payments']);
            });

            // 4. Manajemen Cache & Response
            $message = 'Transaksi berhasil dibuat untuk pelanggan ' . $transaction->customer->customer_name;
            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());
            return redirect()->route('transaction')->with('message', $message);
        } catch (\Exception $error) {
            return back()->withErrors(['message' => 'Terjadi kesalahan sistem: ' . $error->getMessage()]);
        }
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
        $transaction = $transactionModel::with([
            'creator',
            'customer',
            'product',
            'payments' => function ($query) {
                $query->orderBy('payment_date', 'asc');
            }
        ])->findOrFail($id);
        // 1. Cegah pelunasan ganda
        if ($transaction->status === 'repayment') {
            return back()->withErrors(['message' => 'Transaksi sudah lunas.']);
        }

        // 2. Validasi input
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,debit,qris',
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
            return back()->withErrors(['message' => 'Sisa tagihan adalah 0. Tidak perlu pelunasan.']);
        }
        try {
            DB::transaction(function () use ($transaction, $remainingPayment, $validated) {
                // 5. Simpan pembayaran pelunasan
                $transaction->payments()->create([
                    'created_by' => auth()->id(),
                    'payment_date' => now(),
                    'amount' => $remainingPayment,
                    'payment_type' => 'repayment',
                    'payment_method' => $validated['payment_method'],
                ]);
            });
            // 6. Update status transaksi
            $transaction->update([
                'status' => 'repayment',
            ]);
            $transaction->load(['creator', 'customer', 'product', 'payments']);
            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());
            $message = "Pelunasan transaksi {$transaction->invoice} (An. {$transaction->customer->customer_name}) berhasil diproses.";
            return redirect()->route('transaction')->with('message', $message);
        } catch (\Exception $error) {
            return back()->withErrors(['message' => 'Gagal memproses pelunasan: ' . $error->getMessage()]);
        }
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

        if (in_array($transaction->status, ['cancelled', 'repayment'])) {
            $status = $transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas ';
            return redirect()
                ->route('transaction')
                ->with('warning', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        }


        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());
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

        $transaction = $transactionModel::with(['creator', 'customer', 'product', 'payments'])->findOrFail($id);

        if (in_array($transaction->status, ['cancelled', 'repayment'])) {
            $status = $transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas ';
            return redirect()
                ->route('transaction')
                ->with('message', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        }

        // Hitung Harga Final
        $priceFinal = (int) $request['price_original'] - ($request['price_discount'] ?? 0);

        // Hitung total bayar (kecuali pembayaran pertama yang sedang diedit)
        $firstPayment = $transaction->payments()->first();
        $totalPaidOthers = $transaction->payments()->where('payment_id', '!=', $firstPayment->payment_id)->sum('amount');
        $newTotalPaid = $totalPaidOthers + (int) $request['amount'];

        // logic opsional
        if ($request['payment_type'] === 'payment') {
            $dpMin = $priceFinal * 0.5;
            if ($request['amount'] < $dpMin || $request['amount'] > $priceFinal) {
                return back()->withErrors([
                    'amount' => 'Minimal pembayaran Dana pertama adalah Rp ' . number_format($dpMin, 0, ',', '.')
                ]);
            }
        }

        // Cek Validasi Bisnis
        if ($priceFinal < $newTotalPaid) {
            return back()->withErrors([
                'price_original' => 'Harga akhir tidak boleh lebih kecil dari total yang sudah dibayar.'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $transaction, $priceFinal, $firstPayment) {
                // Update Header
                $transaction->update([
                    'invoice'        => $request['invoice'],
                    'customer_id'    => $request['customer_id'],
                    'product_id'     => $request['product_id'],
                    'price_original' => $request['price_original'],
                    'price_discount' => $request['price_discount'] ?? 0,
                    'price_final'    => $priceFinal,
                ]);

                // Update HANYA pembayaran pertama (DP)
                if ($firstPayment) {
                    $firstPayment->update([
                        'amount'         => $request['amount'],
                        'payment_method' => $request['payment_method'],
                    ]);
                }
            });

            $transaction->load(['creator', 'customer', 'product', 'payments']);

            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());

            $message = "Transaksi invoice {$transaction->invoice} untuk pelanggan {$transaction->customer->customer_name} berhasil diperbarui.";
            return redirect()
                ->route('transaction')
                ->with('message', $message);
        } catch (\Exception $error) {
            return back()->withErrors(['message' => 'Gagal memperbarui data: ' . $error->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionModel $transactionModel, string $id)
    {
        $this->authorize('delete', TransactionModel::class);
        $transaction = $transactionModel::with(['creator', 'customer', 'product', 'payments'])->findOrFail($id);
        // Cegah edit transaksi lunas
        if ($transaction->status === 'repayment' || $transaction->status === 'payment' || $transaction->status === 'cancelled') {
            return redirect()
                ->route('transaction')
                ->with('warning', 'Transaksi ini tidak dapat dihapus untuk menjaga konsistensi data.');
        }

        $transaction->delete();

        $message = 'Transaksi tanggal ' . Carbon::parse($transaction->transaction_date)->format('d/m/Y') . ' pelanggan ' . $transaction->customer->customer_name . ' telah dihapus.';
        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());

        return redirect()
            ->route('transaction')
            ->with('message', $message);
    }

    public function destroy_all(Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) {
            return redirect()
                ->route('transaction')
                ->with('warning', 'Tidak ada data yang dipilih.');
        }

        $transaction = TransactionModel::whereIn('transaction_id', $all_id)
            ->where('created_by', auth()->id())
            ->withCount('payments')
            ->get();

        // Cek apakah ada transaksi yang sudah punya payment
        foreach ($transaction as $trx) {
            if ($trx->payments_count > 0) {
                return redirect()
                    ->route('transaction')
                    ->with('warning', 'Sebagian transaksi sudah memiliki pembayaran dan tidak dapat dihapus.');
            }
        }

        // Hapus semua transaksi aman
        TransactionModel::where('created_by', auth()->id())
            ->whereIn('transaction_id', $all_id)
            ->delete();

        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());

        return redirect()
            ->route('transaction')
            ->with('message', count($all_id) . ' transaksi berhasil dihapus.');
    }

    public function cancelled(TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
        $transaction = $transactionModel::with([
            'creator:id,name',
            'customer:customer_id,customer_name,number_phone_customer',
            'product:product_id,name',
            'payments:payment_id,transaction_id,payment_date,payment_type,payment_method,amount'
        ])->findOrFail($id);

        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());

        return Inertia::render('Transaction/Form/TransactionCancelled', [
            'transaction' => $transaction
        ]);
    }
    public function cancelUpdated(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan harus diisi.',
            'reason.min' => 'Alasan pembatalan minimal 10 karakter.',
            'reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        $transaction = $transactionModel::with([
            'creator',
            'customer',
            'product',
            'payments'
        ])->findOrFail($id);

        // if (in_array($transaction->status, ['cancelled', 'repayment'])) {
        //     $status = $transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas ';
        //     return redirect()
        //         ->route('transaction')
        //         ->with('message', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        // }

        try {
            DB::transaction(function () use ($request, $transaction) {
                // 3. Update Status
                $transaction->update([
                    'cancel_reason' => $request->input('reason'),
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id()
                ]);
            });
            $transaction->load(['creator', 'customer', 'product', 'payments']);
            // 4. Manajemen Cache & Redirect
            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());

            return redirect()->route('transaction')
                ->with('message', "Transaksi pelanggan {$transaction->customer->customer_name} berhasil dibatalkan.");
        } catch (\Exception $err) {
            return back()->withErrors(['message' => 'Gagal memproses pembatalan: ' . $err->getMessage()]);
        }
    }

    public function reset()
    {
        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());
        return redirect()->route('transaction')->with('message', 'Data transaksi berhasil diperbarui.');
    }
}
