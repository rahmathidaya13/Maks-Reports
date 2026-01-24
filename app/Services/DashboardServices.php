<?php

namespace App\Services;

use App\Models\TransactionModel;
use Carbon\Carbon;

class DashboardServices
{
    // public function getPaidOffTransactions(int $month, int $year, int $userId)
    // {
    //     $userId = Auth::id();
    //     // Default bulan ini, atau filter dari request
    //     $month = $request->input('month', Carbon::now()->month);
    //     $year = $request->input('year', Carbon::now()->year);

    //     // Target tanggal awal dan akhir bulan yang dipilih
    //     $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    //     $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

    //     // ==========================================================
    //     // 1. LOGIC UTAMA: Mencari Transaksi yang LUNAS di Bulan Ini
    //     // ==========================================================
    //     // Kita cari transaksi milik user ini, yang total bayarnya >= harga final,
    //     // DAN pembayaran terakhirnya jatuh pada range tanggal bulan ini.

    //     $completedTransactions = Transaction::query()
    //         ->where('created_by', $userId)
    //         ->whereHas('payments') // Harus ada pembayaran
    //         ->withSum('payments', 'amount') // Hitung total bayar
    //         ->with(['payments' => function($q) {
    //             $q->orderBy('payment_date', 'desc'); // Ambil tanggal terakhir
    //         }])
    //         ->get()
    //         ->filter(function ($trx) use ($startDate, $endDate) {
    //             // Syarat 1: Harus Lunas (Total Bayar >= Harga Final)
    //             $isPaidOff = $trx->payments_sum_amount >= $trx->price_final;

    //             if (!$isPaidOff) return false;

    //             // Syarat 2: Tanggal Pelunasan (pembayaran terakhir) harus di bulan ini
    //             $lastPaymentDate = Carbon::parse($trx->payments->first()->payment_date);

    //             return $lastPaymentDate->between($startDate, $endDate);
    //         });

    //     // HITUNG METRIK
    //     $totalSalesLunas = $completedTransactions->sum('price_final');
    //     $totalBonus = $totalSalesLunas * 0.01; // 1% Bonus
    //     $totalProductsSold = $completedTransactions->count(); // Asumsi 1 row transaksi = 1 produk terjual

    //     // ==========================================================
    //     // 2. CHART: Statistik Penjualan Harian (Aktivitas Sales)
    //     // ==========================================================
    //     // Ini untuk melihat performa harian sales (berdasarkan tanggal transaksi dibuat)
    //     $dailyStats = Transaction::selectRaw('DATE(transaction_date) as date, SUM(price_final) as total')
    //         ->where('created_by', $userId)
    //         ->whereBetween('transaction_date', [$startDate, $endDate])
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->get();

    //     // ==========================================================
    //     // 3. TOP PRODUCTS (Produk Paling Laku Bulan Ini)
    //     // ==========================================================
    //     $topProducts = Transaction::select('product_id', DB::raw('count(*) as total_sold'))
    //         ->where('created_by', $userId)
    //         ->whereBetween('transaction_date', [$startDate, $endDate])
    //         ->with('product:product_id,name') // Pastikan ada relasi 'product' di model Transaction
    //         ->groupBy('product_id')
    //         ->orderByDesc('total_sold')
    //         ->limit(5)
    //         ->get()
    //         ->map(function($item) {
    //             return [
    //                 'name' => $item->product->name,
    //                 'total' => $item->total_sold
    //             ];
    //         });

    //     // ==========================================================
    //     // 4. LOGIC BANDINGAN BULAN LALU (Untuk Persentase)
    //     // ==========================================================
    //     // (Sederhananya kita ambil total sales lunas bulan lalu dengan logic sama,
    //     // lalu hitung persentase kenaikan/penurunan. Kode dipersingkat agar tidak terlalu panjang).
    //     // ... (Logic sama dengan no 1 tapi startDate/endDate dimundurkan 1 bulan)

    //     // return Inertia::render('Dashboard/Index', [
    //     //     'stats' => [
    //     //         'bonus' => $totalBonus,
    //     //         'sales_volume' => $totalSalesLunas,
    //     //         'products_sold' => $totalProductsSold,
    //     //         'current_month_name' => $startDate->translatedFormat('F Y'),
    //     //     ],
    //     //     'chart_data' => [
    //     //         'labels' => $dailyStats->pluck('date'),
    //     //         'values' => $dailyStats->pluck('total'),
    //     //     ],
    //     //     'top_products' => $topProducts
    //     // ]);
    // }
}
