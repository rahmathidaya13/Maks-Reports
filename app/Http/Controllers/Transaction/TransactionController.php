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
use App\Repositories\AdminDashboardRepository;
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
            'status' => 'nullable|in:all,payment,repayment,cancelled',
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
            ->where('created_by', auth()->id())
            ->get();

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

        // Hitung Sub Total (Harga Barang Murni)
        $subTotal = 0;
        foreach ($request->items as $item) {
            $grossItemPrice = $item['price_original'] * $item['quantity']; // Harga Kotor
            $discountPct = $item['discount_percentage'] ?? 0; // Tangkap angka 10, 11, atau 0
            $nominalDiscount = $grossItemPrice * ($discountPct / 100); // Hitung nominal aslinya
            $itemSubTotal = $grossItemPrice - $nominalDiscount; // Harga Bersih
            $subTotal += $itemSubTotal; // Hitung Sub Total
        }

        // Hitung PPN (Hanya ambil persentasenya dari Request)
        $taxPercentage = $request->tax_percentage ?? 0; // Tangkap angka 10, 11, atau 0
        $taxAmount = $subTotal * ($taxPercentage / 100); // Hitung nominal aslinya
        // Hitung Grand Total (Sub Total + PPN)
        $grandTotal = $subTotal + $taxAmount;


        // Validasi Bisnis (DP)
        if ($request->payment_type === 'payment') {
            $dpMin = $grandTotal * 0.5; // DP minimal 50% dari harga setelah pajak
            if ($request->amount < $dpMin) {
                return back()->withErrors(['amount' => 'Minimal DP 50% adalah Rp ' . number_format($dpMin)]);
            }
            if ($request->amount >= $grandTotal) {
                return back()->withErrors(['amount' => 'Nominal DP tidak boleh melebihi dari harga lunas. Gunakan opsi Lunas Langsung.']);
            }
        }

        try {
            $transaction = DB::transaction(function () use ($request, $grandTotal, $subTotal, $taxAmount, $taxPercentage) {
                // A. Header Transaksi
                $transaction = TransactionModel::create([
                    'created_by'       => auth()->id(),
                    'invoice'          => $request->invoice,
                    'transaction_date' => now(),
                    'customer_id'      => $request->customer_id,
                    'status'           => $request->payment_type === 'repayment' ? 'repayment' : 'payment',

                    'sub_total'        => $subTotal,
                    'tax_percentage'   => $taxPercentage,
                    'tax_amount'       => $taxAmount,
                    'grand_total'      => $grandTotal,
                ]);

                // B. Simpan Detail Item (Looping Insert)
                // Kita mapping dulu agar sesuai nama kolom di DB transaction_items
                $itemsData = [];
                foreach ($request->items as $item) {
                    $grossItemPrice = $item['price_original'] * $item['quantity']; // Harga Kotor
                    $discountPct = $item['discount_percentage'] ?? 0; // Tangkap angka 10, 11, atau 0
                    $nominalDiscount = $grossItemPrice * ($discountPct / 100); // Hitung nominal aslinya
                    $itemsData[] = [
                        'created_by'      => auth()->id(),
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'price_unit'      => $item['price_original'], // Harga Manual
                        'discount_amount' => $nominalDiscount,
                        'subtotal'        => $grossItemPrice - $nominalDiscount,
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
            return redirect()->route('transaction')->with('message', $message);
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
            ->where('created_by', auth()->id())
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
            ->where('created_by', auth()->id())
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
            ->where('created_by', auth()->id())
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
            'customer' => CustomerModel::select('customer_id', 'customer_name')
                ->where('created_by', auth()->id())
                ->get(),
            'product' => ProductModel::select('product_id', 'name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionModel $transactionModel, string $id)
    {
        $this->authorize('edit', TransactionModel::class);
        $this->validationText($request->all());


        $transaction = $transactionModel::with(['creator', 'customer', 'items.product', 'payments'])
            ->where('created_by', auth()->id())
            ->findOrFail($id);

        if (in_array($transaction->status, ['cancelled', 'repayment'])) {
            $status = $transaction->status === 'cancelled' ? 'dibatalkan' : 'lunas ';
            return redirect()
                ->route('transaction')
                ->with('message', 'Transaksi dengan status ' . $status . ' tidak dapat diubah.');
        }

        // Hitung Sub Total Baru (Server Side Calculation)
        $subTotal = 0;
        foreach ($request->items as $item) {
            $grossItemPrice = $item['price_original'] * $item['quantity']; // Harga Kotor
            $discountPct = $item['discount_percentage'] ?? 0;  // Hitung nominal diskon dari persentase
            $nominalDiscount = $grossItemPrice * ($discountPct / 100);
            $itemSubtotal = $grossItemPrice - $nominalDiscount;
            $subTotal += $itemSubtotal;
        }

        // Hitung Pajak
        $taxPercentage = $request->tax_percentage ?? 0;
        $taxAmount = $subTotal * ($taxPercentage / 100);

        // Hitung Grand Total Sebenarnya
        $newGrandTotal = $subTotal + $taxAmount;

        // Validasi Bisnis (Aturan DP 50%)
        // Karena item berubah, Sub Total berubah, maka syarat Min DP juga berubah.
        $inputDP = $request->amount;
        $paymentType = $request->payment_type ?? 'payment';

        if ($paymentType === 'payment') {
            $dpMin = $newGrandTotal * 0.5;
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
        }

        try {
            DB::transaction(function () use ($request, $transaction, $subTotal, $taxPercentage, $taxAmount, $newGrandTotal, $inputDP, $paymentType) {
                // Update Header
                $transaction->update([
                    'invoice'        => $request['invoice'],
                    'customer_id'    => $request['customer_id'],
                    'status'           => 'payment',

                    'sub_total'        => $subTotal,
                    'tax_percentage'   => $taxPercentage,
                    'tax_amount'       => $taxAmount,
                    'grand_total'      => $newGrandTotal,
                ]);

                // B. RESET ITEMS (Wipe & Replace)
                $transaction->items()->delete(); // Update HANYA pembayaran pertama (DP)

                $itemsData = [];
                foreach ($request->items as $item) {
                    $grossItemPrice = $item['price_original'] * $item['quantity']; // Harga Kotor
                    $discountPct = $item['discount_percentage'] ?? 0; // Hitung nominal diskon dari persentase
                    $nominalDiscount = $grossItemPrice * ($discountPct / 100); // Hitung nominal aslinya
                    $itemsData[] = [
                        'created_by'      => auth()->id(),
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'price_unit'      => $item['price_original'],
                        'discount_amount' => $nominalDiscount,
                        'subtotal'        => $grossItemPrice - $nominalDiscount,
                    ];
                }
                $transaction->items()->createMany($itemsData);

                // C. UPDATE PEMBAYARAN
                $payAmount = $paymentType === 'repayment' ? $newGrandTotal : $inputDP;
                // Update satu-satunya record payment yang ada (DP) dengan nominal baru
                $transaction->payments()->updateOrCreate(
                    ['transaction_id' => $transaction->transaction_id],
                    [
                        'created_by'     => auth()->id(),
                        'payment_date'   => now(), // Update tanggal bayar ke saat diedit (opsional)
                        'amount'         => $payAmount, // Nominal DP Baru yang sudah divalidasi
                        'payment_type'   => 'payment', // Tetap payment
                        'payment_method' => $request->payment_method,
                    ]
                );
            });

            $transaction->load(['creator', 'customer', 'items.product', 'payments']);

            $this->transactionRepository->clearCache(auth()->id());
            app(DashboardRepository::class)->clearCache(auth()->id());

            $message = "Transaksi dari invoice {$transaction->invoice} untuk pelanggan {$transaction->customer->customer_name} berhasil diperbarui.";
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
        $transaction = $transactionModel::with(['creator', 'customer', 'items.product', 'payments'])
            ->where('created_by', auth()->id())
            ->findOrFail($id);

        $isDeveloper = auth()->user()->hasRole('developer');
        if (!$isDeveloper) {
            if ($transaction->status === 'payment') {
                return redirect()
                    ->route('transaction')
                    ->with('warning', 'Transaksi dari invoice ' . $transaction->invoice . ' sedang berjalan tidak dapat dihapus');
            }

            if (in_array($transaction->status, ['cancelled', 'repayment'])) {
                return redirect()
                    ->route('transaction')
                    ->with($transaction->status === 'cancelled' ? 'warning' : 'info', 'Transaksi yang sudah ' . ucwords($transaction->status === 'cancelled' ? 'Dibatalkan' : 'Lunas') . ' tidak dapat dihapus');
            }
        }
        // hanya developer yang dapat menghapus
        $transaction->delete();

        $message = 'Transaksi tanggal ' . Carbon::parse($transaction->transaction_date)->format('d/m/Y') . ' pelanggan ' . $transaction->customer->customer_name . ' telah dihapus.';
        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());
        if ($isDeveloper) {
            $message .= " (Dihapus paksa oleh Pengembang)";
        }

        return redirect()
            ->route('transaction')
            ->with('message', $message);
    }

    public function destroy_all(Request $request)
    {
        $this->authorize('delete', TransactionModel::class);
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) {
            return redirect()
                ->route('transaction')
                ->with('warning', 'Tidak ada data yang dipilih.');
        }

        $isDeveloper = auth()->user()->hasRole('developer');
        if (!$isDeveloper) {
            // Optimasi: Cek langsung di database apakah ada salah satu transaksi
            // yang dipilih punya relasi ke payments
            $hasRestrictedTransaction = TransactionModel::query()
                ->with(['creator', 'customer', 'items.product', 'payments'])
                ->whereIn('transaction_id', $all_id)
                ->where('created_by', auth()->id()) // Pastikan hanya cek milik sendiri
                ->whereHas('payments') // Cek apakah punya pembayaran
                ->exists(); // Return true/false (Sangat Cepat)

            if ($hasRestrictedTransaction) {
                return redirect()
                    ->route('transaction')
                    ->with('warning', 'Sebagian transaksi sudah memiliki pembayaran dan tidak dapat dihapus.');
            }
        }

        // Hapus semua transaksi aman
        $query = TransactionModel::with(['creator', 'customer', 'items.product', 'payments'])
            ->where('created_by', auth()->id())
            ->whereIn('transaction_id', $all_id);


        $deletedCount = $query->delete();

        $this->transactionRepository->clearCache(auth()->id());
        app(DashboardRepository::class)->clearCache(auth()->id());

        $message = $deletedCount . ' transaksi berhasil dihapus.';

        if ($isDeveloper && $deletedCount > 0) {
            $message .= ' (Mode Developer)';
        }
        return redirect()
            ->route('transaction')
            ->with('message', $message);
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
