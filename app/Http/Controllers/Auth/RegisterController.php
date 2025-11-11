<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BranchesModel;
use App\Models\RolesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register()
    {
        return Inertia::render('Auth/Register',);
    }

    public function validationText($request)
    {
        return Validator::make($request, [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/u' // hanya huruf & spasi
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:50',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // Password minimal ada huruf besar, huruf kecil, angka, simbol
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/'
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 50 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',

            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 50 karakter.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Ulangi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, kecil, angka, dan simbol.',

        ])->validate();
    }
    public function store(Request $request): RedirectResponse
    {
        $this->validationText($request->all());
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $user->profile()->create();
        $user->assignRole('user');
        event(new Registered($user));

        Auth::login($user, true);

        return redirect(RouteServiceProvider::HOME);
    }
}
