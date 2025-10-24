<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


class RecaptchaService
{
    public static function verify($token)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $token,
        ]);

        $data = $response->json();

        return $data['success'] ?? false;
    }
}
