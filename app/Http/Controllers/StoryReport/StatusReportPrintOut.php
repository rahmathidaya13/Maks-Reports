<?php

namespace App\Http\Controllers\StoryReport;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\StatusReportUpdate;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StoryStatusReportModel;
use Barryvdh\DomPDF\Facade\Pdf;

class StatusReportPrintOut extends Controller
{
    public function printToExcel()
    {
        // dd('hello world');
        $fileName = 'Laporan_Status_Update_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StatusReportUpdate, $fileName);
    }
    public function printToPdf()
    {
        $user = auth()->user();
        $branchName = $user->profile->branch->name ?? '-';
        $jobTitle = $user->profile->jobTitle->title ?? '-';

        $reportStatus = StoryStatusReportModel::with('creator')
            ->where('created_by', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->get()
            ->map(function ($report, $index) {
                return [
                    'no' => $index + 1,
                    'kode' => $report->report_code,
                    'tanggal' => Carbon::parse($report->report_date)->translatedFormat('l, d-m-Y'),
                    'jam' => Carbon::parse($report->report_time)->format('H:i'),
                    'jumlah_status' => $report->count_status,
                ];
            });

        $pdfLoad = Pdf::loadView('pdf.ReportToPdf', [
            'headers' => ['No', 'Kode Status', 'Tanggal', 'Jam', 'Jumlah'],
            'data' => $reportStatus,
            'title' => 'Laporan Harian Update Status',
            'subtitle' => 'PT. Toko Maksindo Cabang ' . ucwords($branchName),
            'dibuat' => $user->name,
            'total' => $reportStatus->sum('jumlah_status'),
            'info' => [
                'Tanggal: ' . Carbon::now()->translatedFormat('l, d/m/Y'),
                'Periode: Minggu ke-' . Carbon::now()->weekOfMonth,
                'Divisi: ' . $jobTitle,
            ],
            'logo' => public_path('storage/logo/logo.jpg'),
            'logo_width' => '420px',
            'logo_opacity' => 0.06,
            'logo_top' => '50%',
            'logo_left' => '50%',

        ]);
        $pdfLoad->setPaper('A4', 'portrait');
        return $pdfLoad->stream('laporan_update_status ' . now()->format('Ymd_His') . '.pdf');
    }
}
