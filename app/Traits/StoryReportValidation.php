<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait StoryReportValidation
{
    public function validationText($request)
    {
        return Validator::make($request, [
            'report_date' => ['required', 'date'],
            'report_time' => ['required', 'date_format:H:i'],
            'count_status' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [

            'report_date.required' => 'Tanggal wajib diisi.',
            'report_date.date' => 'Tanggal tidak valid.',
            'report_time.required' => 'Waktu wajib diisi.',
            'report_time.date_format' => 'Waktu tidak valid.',
            'count_status.required' => 'Jumlah status wajib diisi.',
            'count_status.integer' => 'Jumlah status harus berupa angka.',
            'count_status.min' => 'Jumlah status minimal 0.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ])->validate();
    }
}
