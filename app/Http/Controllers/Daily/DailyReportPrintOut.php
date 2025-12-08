<?php

namespace App\Http\Controllers\Daily;

use App\Exports\DailyReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DailyReportModel;
use App\Http\Controllers\Controller;

class DailyReportPrintOut extends Controller
{
    public function printToExcel(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $fileName = 'Laporan_Leads_Harian_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new DailyReport($start_date, $end_date), $fileName);
    }
    public function printToPdf(Request $request)
    {
        $request->validate([
            'start_date_dw' => 'date',
            'end_date_dw' => 'date',
        ]);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $user = auth()->user();
        $branchName = $user->profile->branch->name ?? '-';
        $jobTitle = $user->profile->jobTitle->title ?? '-';
        $dailyReport = DailyReportModel::with('creator')
            ->where('created_by', $user->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get()
            ->map(function ($report, $index) {
                return [
                    'no' => $index + 1,
                    'tanggal' => Carbon::parse($report->date)->translatedFormat('d-m-Y'),
                    'leads' => $report->leads ?? 0,
                    'closing' => $report->closing ?? 0,
                    'fu_yesterday' => $report->fu_yesterday ?? 0,
                    'fu_yesterday_closing' => $report->fu_yesterday_closing ?? 0,
                    'fu_before_yesterday' => $report->fu_before_yesterday ?? 0,
                    'fu_before_yesterday_closing' => $report->fu_before_yesterday_closing ?? 0,
                    'fu_last_week' => $report->fu_last_week ?? 0,
                    'fu_last_week_closing' => $report->fu_last_week_closing ?? 0,
                    'engage_old_customer' => $report->engage_old_customer ?? 0,
                    'engage_closing' => $report->engage_closing ?? 0,
                ];
            });

        $weekStart = Carbon::parse($start_date)->weekOfMonth;
        $weekEnd = Carbon::parse($end_date)->weekOfMonth;

        $pdfLoad = Pdf::loadView('pdf.ReportToPdf', [
            'headers' => ['No', 'Tanggal', 'Leads', 'Closing', 'FU Konsumen Kemarin (H-1)', 'Closing', 'FU Konsumen Kemarennya (H-2)', 'Closing', 'FU Konsumen Minggu Kemarennya', 'Closing', 'Engage Konsumen Lama', 'Closing'],
            'data' => $dailyReport,
            'title' => 'Laporan Leads Harian',
            'subtitle' => 'PT. Toko Maksindo Cabang ' . ucwords($branchName),
            'dibuat' => $user->name,
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
            'totalCheck' => false,
            'total' => [
                '',
                'Total',
                $dailyReport->sum('leads'),
                $dailyReport->sum('closing'),
                $dailyReport->sum('fu_yesterday'),
                $dailyReport->sum('fu_yesterday_closing'),
                $dailyReport->sum('fu_before_yesterday'),
                $dailyReport->sum('fu_before_yesterday_closing'),
                $dailyReport->sum('fu_last_week'),
                $dailyReport->sum('fu_last_week_closing'),
                $dailyReport->sum('engage_old_customer'),
                $dailyReport->sum('engage_closing'),
            ]
        ]);
        // $pdfLoad = Pdf::loadView('pdf.ReportDailyLeads', [
        //     'title' => 'Laporan Summary Leads',
        //     'subtitle' => 'PT. Contoh - Cabang X',
        //     'row' => (object) [
        //         'leads' => $dailyReport->sum('leads'),
        //         'closing' => $dailyReport->sum('closing'),
        //         'fu_yesterday' => $dailyReport->sum('fu_yesterday'),
        //         'fu_yesterday_closing' => $dailyReport->sum('fu_yesterday_closing'),
        //         'fu_before_yesterday' => $dailyReport->sum('fu_before_yesterday'),
        //         'fu_before_yesterday_closing' => $dailyReport->sum('fu_before_yesterday_closing'),
        //         'fu_last_week' => $dailyReport->sum('fu_last_week'),
        //         'fu_last_week_closing' => $dailyReport->sum('fu_last_week_closing'),
        //         'engage_old_customer' => $dailyReport->sum('engage_old_customer'),
        //         'engage_closing' => $dailyReport->sum('engage_closing'),
        //     ]
        // ]);
        $pdfLoad->setPaper('A4', 'portrait');
        return $pdfLoad->stream('Laporan_Leads_Harian ' . now()->format('Ymd_His') . '.pdf');
    }
    public function information(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if (!$start_date || !$end_date) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid date'
            ]);
        }

        $user = auth()->user();
        $dailyReport = DailyReportModel::with('creator')
            ->where('created_by', $user->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->get();

        return response()->json([
            'status' => true,
            'total_rows' => $dailyReport->count(),
            'total_leads' => $dailyReport->sum('leads'),
            'total_closing' => $dailyReport->sum('closing'),
            'total_fu' =>
                $dailyReport->sum('fu_yesterday')
                + $dailyReport->sum('fu_before_yesterday')
                + $dailyReport->sum('fu_last_week'),
            'total_fu_closing' =>
                $dailyReport->sum('fu_yesterday_closing')
                + $dailyReport->sum('fu_before_yesterday_closing')
                + $dailyReport->sum('fu_last_week_closing'),
            'total_engage_old_customer' => $dailyReport->sum('engage_old_customer'),
            'total_engage_closing' => $dailyReport->sum('engage_closing'),
            'first_date' => Carbon::parse($start_date)->translatedFormat('l, d-m-Y'),
            'last_date' => Carbon::parse($end_date)->translatedFormat('l, d-m-Y'),
            'week_start' => Carbon::parse($start_date)->weekOfMonth,
            'week_end' => Carbon::parse($end_date)->weekOfMonth,
        ]);
    }
}
