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
        // dd($request->all());
        // $this->authorize('create', TransactionModel::class);
        $request->validate([
            'invoice' => 'required|max:25',
            'customer_id' => 'required',
            'items' => 'required|array|min:1', // Wajib array dan minimal 1
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_original' => 'required|numeric|min:0', // Harga Manual
            'items.*.price_discount' => 'nullable|numeric|min:0', // Diskon Manual

            'payment_type' => 'required|in:payment,repayment',
            'payment_method' => 'required|in:cash,transfer,debit,qris',
            'amount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
        ]);

        // Hitung Grand Total (Looping Server Side)
        // Jangan percaya total dari frontend, hitung ulang di backend demi keamanan
        $grandTotal = 0;
        foreach ($request->items as $item) {
            $subtotal = ($item['price_original'] * $item['quantity']) - ($item['price_discount'] ?? 0);
            $grandTotal += $subtotal;
        }

        // Validasi Bisnis (DP)
        if ($request->payment_type === 'payment') {
            $dpMin = $grandTotal * 0.5;
            if ($request->amount < $dpMin) {
                return back()->withErrors(['amount' => 'Minimal DP 50% adalah Rp ' . number_format($dpMin)]);
            }
            if ($request->amount >= $grandTotal) {
                return back()->withErrors(['amount' => 'Nominal DP tidak boleh lunas. Gunakan opsi Lunas Langsung.']);
            }
        }

        try {
            $transaction = DB::transaction(function () use ($request, $grandTotal) {
                // A. Header Transaksi
                $transaction = TransactionModel::create([
                    'created_by'       => auth()->id(),
                    'invoice'          => $request->invoice,
                    'transaction_date' => now(),
                    'customer_id'      => $request->customer_id,
                    'status'           => $request->payment_type === 'repayment' ? 'repayment' : 'payment',
                    'grand_total'      => $grandTotal,
                ]);

                // B. Simpan Detail Item (Looping Insert)
                // Kita mapping dulu agar sesuai nama kolom di DB transaction_items
                $itemsData = [];
                foreach ($request->items as $item) {
                    $itemsData[] = [
                        'created_by'      => auth()->id(),
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'price_unit'      => $item['price_original'], // Harga Manual
                        'discount_amount' => $item['price_discount'] ?? 0,
                        'subtotal'        => ($item['price_original'] * $item['quantity']) - ($item['price_discount'] ?? 0),
                    ];
                }
                $transaction->items()->createMany($itemsData);
                // C. Simpan Pembayaran
                $payAmount = $request->payment_type === 'repayment' ? $grandTotal : $request->amount;

                $transaction->payments()->create([
                    'created_by'     => auth()->id(),
                    'payment_date'   => now(),
                    'amount'         => $payAmount,
                    'payment_type'   => $request->payment_type === 'repayment' ? 'repayment' : 'payment',
                    'payment_method' => $request->payment_method,
                ]);

                return $transaction->load(['creator', 'items.product', 'customer', 'payments']);
            });

            // D. Manajemen Cache & Response
            $message = 'Transaksi berhasil dibuat untuk pelanggan ' . $transaction->customer->customer_name;
            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());
            return redirect()->route('customers')->with('message', $message);
        } catch (\Exception $error) {
            return back()->withErrors(['message' => 'Terjadi kesalahan sistem: ' . $error->getMessage()]);
        }
    }

    public function show(TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
        $transaction = $transaction = $transactionModel::with([
            'creator',
            'customer',
            'payments',
            'items.product'
        ])
            ->find($id);
        $this->transactionRepository->clearCache(auth()->id());
        return Inertia::render('Transaction/Form/RepaymentForm', [
            'transaction' => $transaction
        ]);
    }
    public function settle(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
        // Validasi input
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,debit,qris',
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $transaction = $transactionModel::with([
            'creator:id,name',
            'customer:customer_id,customer_name,number_phone_customer',
            'payments:payment_id,transaction_id,payment_date,payment_type,payment_method,amount'
        ])
            ->with(['items.product'])
            ->findOrFail($id);
        // 1. Cegah pelunasan ganda
        if ($transaction->status === 'repayment') {
            return back()->withErrors(['message' => 'Transaksi sudah lunas.']);
        }


        // Hitung total yang sudah dibayar
        $totalPaid = (int) $transaction->payments()->sum('amount');

        // Hitung sisa pembayaran
        $remainingPayment = $transaction->grand_total - $totalPaid;

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

            $transaction->load(['creator', 'items.product', 'customer', 'payments']);
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
        $transaction = $transactionModel::with([
            'creator:id,name',
            'customer:customer_id,customer_name,number_phone_customer',
            'payments:payment_id,transaction_id,payment_date,payment_type,payment_method,amount'
        ])
            ->with(['items.product'])
            ->findOrFail($id);

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
            'customer' => CustomerModel::select('customer_id', 'customer_name')->get(),
            'product' => ProductModel::select('product_id', 'name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);

        $request->validate([
            'invoice' => 'required|max:25',
            'customer_id' => 'required',
            'items' => 'required|array|min:1', // Wajib array dan minimal 1
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_original' => 'required|numeric|min:0', // Harga Manual
            'items.*.price_discount' => 'nullable|numeric|min:0', // Diskon Manual

            'payment_type' => 'required|in:payment,repayment',
            'payment_method' => 'required|in:cash,transfer,debit,qris',
            'amount' => 'required_if:payment_type,payment|nullable|numeric|min:0',
        ]);

        $transaction = $transactionModel::with(['creator', 'customer', 'items.product', 'payments'])->findOrFail($id);

        if (in_array($transaction->status, ['cancelled', 'repayment'])) {
            $status = $transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas ';
            return redirect()
                ->route('transaction')
                ->with('message', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        }

        // Hitung Grand Total Baru (Server Side Calculation)
        $newGrandTotal = 0;
        foreach ($request->items as $item) {
            $subtotal = ($item['price_original'] * $item['quantity']) - ($item['price_discount'] ?? 0);
            $newGrandTotal += $subtotal;
        }

        // Validasi Bisnis (Aturan DP 50%)
        // Karena item berubah, Grand Total berubah, maka syarat Min DP juga berubah.
        $dpMin = $newGrandTotal * 0.5;
        $inputDP = $request->amount;


        // Cek Min 50%
        if ($inputDP < $dpMin) {
            return back()->withErrors([
                'amount' => 'Karena total belanja berubah menjadi Rp ' . number_format($newGrandTotal) . ', maka Minimal DP (50%) adalah Rp ' . number_format($dpMin)
            ]);
        }

        // Cek Maksimal (Tidak boleh lunas di sini)
        // Karena kalau lunas, harusnya lewat menu pelunasan
        if ($inputDP >= $newGrandTotal) {
            return back()->withErrors([
                'amount' => 'Nominal DP tidak boleh menyamai/melebihi Total Tagihan. Jika ingin melunasi, silakan simpan dulu lalu masuk ke menu Pelunasan.'
            ]);
        }


        try {
            DB::transaction(function () use ($request, $transaction, $newGrandTotal, $dpMin, $inputDP) {
                // Update Header
                $transaction->update([
                    'invoice'        => $request['invoice'],
                    'customer_id'    => $request['customer_id'],
                    'grand_total'    => $newGrandTotal,
                    'status'           => 'payment',
                ]);

                // B. RESET ITEMS (Wipe & Replace)
                $transaction->items()->delete(); // Update HANYA pembayaran pertama (DP)

                $itemsData = [];
                foreach ($request->items as $item) {
                    $itemsData[] = [
                        'created_by'      => auth()->id(),
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'price_unit'      => $item['price_original'],
                        'discount_amount' => $item['price_discount'] ?? 0,
                        'subtotal'        => ($item['price_original'] * $item['quantity']) - ($item['price_discount'] ?? 0),
                    ];
                }
                $transaction->items()->createMany($itemsData);

                // C. UPDATE PEMBAYARAN
                // Update satu-satunya record payment yang ada (DP) dengan nominal baru
                $transaction->payments()->updateOrCreate(
                    ['transaction_id' => $transaction->transaction_id],
                    [
                        'created_by'     => auth()->id(),
                        'payment_date'   => now(), // Update tanggal bayar ke saat diedit (opsional)
                        'amount'         => $inputDP, // Nominal DP Baru yang sudah divalidasi
                        'payment_type'   => 'payment', // Tetap payment
                        'payment_method' => $request->payment_method,
                    ]
                );
            });

            $transaction->load(['creator', 'customer', 'items.product', 'payments']);

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
        if ($transaction->status === 'payment') {
            return redirect()
                ->route('transaction')
                ->with('warning', 'Transaksi ini sedang berjalan tidak dapat dihapus');
        }

        if (in_array($transaction->status, ['cancelled', 'repayment'])) {
            return redirect()
                ->route('transaction')
                ->with($transaction->status === 'cancelled' ? 'warning' : 'info', 'Transaksi yang sudah ' . ucwords($transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas') . ' tidak dapat dihapus');
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
            'payments:payment_id,transaction_id,payment_date,payment_type,payment_method,amount',
            'items.product'
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
            'items.product',
            'payments'
        ])->findOrFail($id);

        if ($transaction->status === 'cancelled') {
            $status = $transaction->status === 'cancelled' ? 'Dibatalkan' : 'Lunas ';
            return redirect()
                ->route('transaction')
                ->with('message', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        }

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
            $transaction->load(['creator', 'customer', 'items.product', 'payments']);
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
