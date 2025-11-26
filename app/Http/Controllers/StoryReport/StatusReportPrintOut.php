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
            ->get()
            ->map(function ($report, $index) {
                return [
                    'no' => $index + 1,
                    'nama' => $report->creator->name,
                    'kode' => $report->report_code,
                    'tanggal' => Carbon::parse($report->report_date)->translatedFormat('l, d-m-Y'),
                    'jam' => Carbon::parse($report->report_time)->format('H:i'),
                    'jumlah_status' => $report->count_status,
                ];
            });

        $pdfLoad = Pdf::loadView('pdf.status_report', [
            'nama' => $reportStatus[0]['nama'],
            'branch' => $branchName,
            'reports' => $reportStatus,
            'date_now' => Carbon::now()->translatedFormat('l, d-m-Y'),
            'total' => $reportStatus->sum('jumlah_status'),
        ])->setPaper('A4', 'portrait');
        return $pdfLoad->stream('laporan_status.pdf');
    }
}
