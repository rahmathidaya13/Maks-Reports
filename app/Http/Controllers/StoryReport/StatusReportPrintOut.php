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
    public function printToExcel(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $fileName = 'Laporan_Update_Status_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StatusReportUpdate($start_date, $end_date), $fileName);
    }
    public function printToPdf(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $user = auth()->user();
        $branchName = $user->profile->branch->name ?? '-';
        $jobTitle = $user->profile->jobTitle->title ?? '-';

        $reportStatus = StoryStatusReportModel::with('creator')
            ->where('created_by', $user->id)
            ->whereBetween('report_date', [$start_date, $end_date])
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

        $weekStart = Carbon::parse($start_date)->weekOfMonth;
        $weekEnd   = Carbon::parse($end_date)->weekOfMonth;

        $pdfLoad = Pdf::loadView('pdf.ReportToPdf', [
            'headers' => ['No', 'Kode Status', 'Tanggal', 'Jam', 'Jumlah'],
            'data' => $reportStatus,
            'title' => 'Laporan Harian Update Status',
            'subtitle' => 'PT. Toko Maksindo Cabang ' . ucwords($branchName),
            'dibuat' => $user->name,
            'total' => $reportStatus->sum('jumlah_status'),
            'info' => [
                'Tanggal: ' . Carbon::now()->translatedFormat('l, d/m/Y'),
                'Periode: Minggu ke-' . $weekStart . ($weekStart != $weekEnd ? ' s/d ' . $weekEnd : ''),
                'Divisi: ' . $jobTitle,
            ],
            'logo' => public_path('storage/logo/logo.jpg'),
            'logo_width' => '420px',
            'logo_opacity' => 0.05,
            'logo_top' => '50%',
            'logo_left' => '50%',

        ]);
        $pdfLoad->setPaper('A4', 'portrait');
        return $pdfLoad->stream('Laporan_Update_Status ' . now()->format('Ymd_His') . '.pdf');
    }
}
