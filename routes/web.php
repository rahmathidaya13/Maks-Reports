<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthGoogle\GoogleAuthController;
use App\Http\Controllers\Branches\BranchesController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Roles\RolesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware(['guest'])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login/store', 'store')->name('login.store');
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register/store', 'store')->name('register.store');
    });

    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('/forgot-password', 'create')->name('forgot.create');
        Route::post('/forgot-password/store', 'store')->name('forgot.store');
    });
    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('/reset-password/{token}', 'create')->name('password.reset');
        Route::post('/reset-password/store', 'store')->name('password.store');
    });

    // AUTH GOOGLE
    Route::get("/auth/google", [GoogleAuthController::class, 'redirect'])->name('google.login');
    Route::get("/auth/google/callback", [GoogleAuthController::class, 'callback'])->name('google.callback');
});

// Akun yang sudah diverfikasi
Route::middleware(['auth', 'role:develop,admin,user', 'verified', 'profile.completed'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home');
    });
    Route::controller(RolesController::class)->group(function () {
        Route::get('/roles/list', 'index')->name('roles')->middleware('can:view');
        Route::get('/roles/create', 'create')->name('roles.create')->middleware('can:add');
        Route::post('/roles/store', 'store')->name('roles.store')->middleware('can:add');
        Route::get('/roles/edit/{id}', 'edit')->name('roles.edit')->middleware('can:edit');
        Route::put('/roles/update/{id}', 'update')->name('roles.update')->middleware('can:edit');
        Route::get('/roles/destroy/{id}', 'destroy')->name('roles.destroy')->middleware('can:delete,onlyDeveloper');
    });
    Route::controller(BranchesController::class)->group(function () {
        Route::get('/branches/list', 'index')->name('branches');
    });
});

Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile/completed', 'edit')->name('profile');
    Route::post('/profile/update/{id}', 'update')->name('profile.update');
});
Route::controller(ConfirmPasswordController::class)->group(function () {
    Route::get('/confirm-password', 'showConfirmForm')->name('confirm.password');
    Route::post('/confirm-password/send', 'confirm')->name('confirm.password.send');
});

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice')->middleware('auth');
    Route::get('/email/verify/{id}/{hash}', 'verify')->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', 'resend')->middleware(['throttle:6,1'])->name('verification.send');
});
