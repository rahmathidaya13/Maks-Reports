<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
trait DailyReport
{
    public function validationText($request)
    {
        return Validator::make($request, [
            'leads' => [
                'required',
                'integer',
                'min:0',
                'max:1000'
            ],

            'closing' => [
                'required',
                'integer',
                'min:0',
                'max:1000'
            ],

            'fu_yesterday' => ['required', 'integer', 'min:0', 'max:1000'],
            'fu_yesterday_closing' => ['required', 'integer', 'min:0', 'max:1000'],

            'fu_before_yesterday' => ['required', 'integer', 'min:0', 'max:1000'],
            'fu_before_yesterday_closing' => ['required', 'integer', 'min:0', 'max:1000'],

            'fu_last_week' => ['required', 'integer', 'min:0', 'max:1000'],
            'fu_last_week_closing' => ['required', 'integer', 'min:0', 'max:1000'],

            'engage_old_customer' => ['required', 'integer', 'min:0', 'max:1000'],
            'engage_closing' => ['required', 'integer', 'min:0', 'max:1000'],
        ], [

            'leads.required' => 'Kolom jumlah Leads tidak boleh kosong.',
            'leads.integer' => 'Jumlah Leads harus berupa bilangan bulat.',
            'leads.min' => 'Jumlah Leads minimal adalah 0',
            'leads.max' => 'Jumlah Leads melebihi batas maksimal yang diizinkan max:1000.',

            'closing.required' => 'Kolom jumlah Closing tidak boleh kosong.',
            'closing.integer' => 'Jumlah Closing harus berupa bilangan bulat.',
            'closing.min' => 'Jumlah Closing minimal adalah 0',
            'closing.max' => 'Jumlah Closing melebihi batas maksimal yang diizinkan max:1000.',

            'fu_yesterday.required' => 'FU Konsumen Kemarin tidak boleh kosong.',
            'fu_yesterday.integer' => 'FU Konsumen Kemarin harus berupa bilangan bulat.',
            'fu_yesterday.min' => 'FU Konsumen Kemarin minimal adalah 0',
            'fu_yesterday.max' => 'FU Konsumen Kemarin melebihi batas maksimal yang diizinkan max:1000.',

            'fu_yesterday_closing.required' => 'Closing dari FU Kemarin tidak boleh kosong.',
            'fu_yesterday_closing.integer' => 'Closing dari FU Kemarin harus berupa bilangan bulat.',
            'fu_yesterday_closing.min' => 'Closing dari FU Kemarin minimal adalah 0',
            'fu_yesterday_closing.max' => 'Closing dari FU Kemarin melebihi batas maksimal yang diizinkan max:1000.',

            'fu_before_yesterday.required' => 'FU Konsumen Kemarinnya tidak boleh kosong.',
            'fu_before_yesterday.integer' => 'FU Konsumen Kemarinnya harus berupa bilangan bulat.',
            'fu_before_yesterday.min' => 'FU Konsumen Kemarinnya minimal adalah 0',
            'fu_before_yesterday.max' => 'FU Konsumen Kemarinnya melebihi batas maksimal yang diizinkan max:1000.',

            'fu_before_yesterday_closing.required' => 'Closing dari FU Kemarinnya tidak boleh kosong.',
            'fu_before_yesterday_closing.integer' => 'Closing dari FU Kemarinnya harus berupa bilangan bulat.',
            'fu_before_yesterday_closing.min' => 'Closing dari FU Kemarinnya minimal adalah 0',
            'fu_before_yesterday_closing.max' => 'Closing dari FU Kemarinnya melebihi batas maksimal yang diizinkan max:1000.',

            'fu_last_week.required' => 'FU Konsumen Minggu Kemarinnya tidak boleh kosong.',
            'fu_last_week.integer' => 'FU Konsumen Minggu Kemarinnya harus berupa bilangan bulat.',
            'fu_last_week.min' => 'FU Konsumen Minggu Kemarinnya minimal adalah 0',
            'fu_last_week.max' => 'FU Konsumen Minggu Kemarinnya melebihi batas maksimal yang diizinkan max:1000.',


            'fu_last_week_closing.required' => 'Closing dari FU Minggu Kemarinnya tidak boleh kosong.',
            'fu_last_week_closing.integer' => 'Closing dari FU Minggu Kemarinnya harus berupa bilangan bulat.',
            'fu_last_week_closing.min' => 'Closing dari FU Minggu Kemarinnya minimal adalah 0',
            'fu_last_week_closing.max' => 'Closing dari FU Minggu Kemarinnya melebihi batas maksimal yang diizinkan max:1000.',

            'engage_old_customer.required' => 'Engage Pelanggan Lama tidak boleh kosong.',
            'engage_old_customer.integer' => 'Engage Pelanggan Lama harus berupa bilangan bulat.',
            'engage_old_customer.min' => 'Engage Pelanggan Lama minimal adalah 0',
            'engage_old_customer.max' => 'Engage Pelanggan Lama melebihi batas maksimal yang diizinkan max:1000.',

            'engage_closing.required' => 'Closing dari Engage Pelanggan Lama tidak boleh kosong.',
            'engage_closing.integer' => 'Closing dari Engage Pelanggan Lama harus berupa bilangan bulat.',
            'engage_closing.min' => 'Closing dari Engage Pelanggan Lama minimal adalah 0',
            'engage_closing.max' => 'Closing dari Engage Pelanggan Lama melebihi batas maksimal yang diizinkan max:1000.',

        ])->validate();
    }
}
