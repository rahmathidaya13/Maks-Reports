<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait StoryReportValidation
{
    public function validationText($request)
    {
        return Validator::make($request, [
            'report' => 'required|array|min:1',
            'report.*.report_time' => 'required|date_format:H:i',
            'report.*.count_status' => 'required|numeric|min:0'
        ], [

            'report.*.report_time.required' => 'Waktu harus diisi',
            'report.*.report_time.date_format' => 'Waktu harus dalam format H:i',
            'report.*.count_status.required' => 'Jumlah harus diisi',
            'report.*.count_status.numeric' => 'Jumlah harus angka',
            'report.*.count_status.min' => 'Jumlah minimal 0',
        ])->validate();
    }
}
