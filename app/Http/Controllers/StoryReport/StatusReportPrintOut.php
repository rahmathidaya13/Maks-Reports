<?php

namespace App\Http\Controllers\StoryReport;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\StatusReportUpdate;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class StatusReportPrintOut extends Controller
{
    public function printToExcel()
    {
        // dd('hello world');
        $fileName = 'Laporan_Status_Update_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StatusReportUpdate, $fileName);

    }
}
