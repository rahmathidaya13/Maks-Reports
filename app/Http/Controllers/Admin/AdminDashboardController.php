<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminDashboardExport;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\AdminDashboardRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['admin.access']);
    }
    public function index(Request $request, AdminDashboardRepository $dashboardRepository)
    {
        $isFilter = $request->filled(['start_date', 'end_date']);
        if ($isFilter) {
            $startOfMonth = Carbon::parse($request['start_date'])->startOfDay();
            $endOfMonth = Carbon::parse($request['end_date'])->endOfDay();
        } else {
            $now = Carbon::now();
            $startOfMonth =  $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
        }
        // dd($startOfMonth, $endOfMonth);
        return Inertia::render(
            'Admin/Index',
            array_merge(
                $dashboardRepository->getDashboard(auth()->id(), $startOfMonth, $endOfMonth),
                [
                    'filters' => [
                        'start_date' => $startOfMonth->format('Y-m-d'),
                        'end_date' => $endOfMonth->format('Y-m-d'),
                    ]
                ]
            ),
        );
    }
    public function reset()
    {
        app(AdminDashboardRepository::class)->clearCache(auth()->id());
        return redirect()->route('admin.dashboard.index')->with('message', 'Data dashboard berhasil diperbarui.');
    }

    public function export(Request $request, AdminDashboardRepository $repository)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        // 1. Tentukan tanggal (Bisa ambil dari request filter jika ada, atau default bulan ini)
        $startOfMonth = $request['filters'] ? Carbon::parse($request['filters']['start_date'])->startOfDay() : now()->startOfMonth();
        $endOfMonth = $request['filters'] ? Carbon::parse($request['filters']['end_date'])->endOfDay() : now()->endOfMonth();

        // 2. Ambil data dari Repository yang sudah kita buat sebelumnya
        $data = $repository->getDashboard(auth()->id(), $startOfMonth, $endOfMonth);

        $formatChecks = array('pdf');
        if (!in_array($request['format'], $formatChecks)) {
            return redirect()->route('admin.dashboard.index')->withErrors(['error' => 'Format export tidak valid.']);
        }

        // export pdf
        if ($request['format'] == 'pdf') {
            $pdf = Pdf::loadView('exports.admin_dashboard', [
                'title' => 'Laporan Manajemen Transaksi',
                'data' => $data,
                'startOfMonth' => $startOfMonth,
                'endOfMonth' => $endOfMonth,
                'adminName' => ucwords(auth()->user()->name),
                'logo' => public_path('storage/logo/logo.jpg')
            ])->setPaper('a4', 'portrait');

            return $pdf->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->stream('Laporan_Manajemen_Transaksi_' . now()->format('Ymd_His') . '.pdf');
        }

        // export excel
        if ($request['format'] == 'excel') {
            $fileName = 'Laporan_Manajemen_Transaksi_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new AdminDashboardExport($data, $startOfMonth, $endOfMonth), $fileName);
        }
        return back()->withErrors(['error' => 'Format export tidak valid.']);
    }
}
