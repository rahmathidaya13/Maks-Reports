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
use App\Http\Controllers\Authority\AuthorityController;
use App\Http\Controllers\Authorization\PermissionController;
use App\Http\Controllers\Authorization\RoleController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\Branches\BranchesController;
use App\Http\Controllers\Daily\DailyReportController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Job\JobTitleController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Roles\RolesController;
use App\Http\Controllers\StoryReport\StoryStatusReportController;

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
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home');
    });

    Route::controller(JobTitleController::class)->group(function () {
        Route::get('/job_title/list', 'index')->name('job_title');
        Route::get('/job_title/create', 'create')->name('job_title.create');
        Route::post('/job_title/store', 'store')->name('job_title.store');
        Route::get('/job_title/edit/{id}', 'edit')->name('job_title.edit');
        Route::put('/job_title/update/{id}', 'update')->name('job_title.update');
        Route::delete('/job_title/destroy/{id}', 'destroy')->name('job_title.deleted');
        Route::post('/job_title/delete_all', 'destroy_all')->name('job_title.destroy_all');
    });
    Route::controller(BranchesController::class)->group(function () {
        Route::get('/branches/list', 'index')->name('branches');
    });
    // laporan harian
    Route::controller(DailyReportController::class)->group(function () {
        Route::get('/daily_report/list', 'index')->name('daily_report');
        Route::get('/daily_report/create', 'create')->name('daily_report.create');
        Route::post('/daily_report/store', 'store')->name('daily_report.store');
        Route::get('/daily_report/edit/{id}', 'edit')->name('daily_report.edit');
        Route::put('/daily_report/update/{id}', 'update')->name('daily_report.update');
        Route::delete('/daily_report/destroy/{id}', 'destroy')->name('daily_report.deleted');
        Route::post('/daily_report/delete_all', 'destroy_all')->name('daily_report.destroy_all');
    });
    Route::controller(StoryStatusReportController::class)->group(function () {
        Route::get('/story_report/list', 'index')->name('story_report');
        Route::get('/story_report/create', 'create')->name('story_report.create');
        Route::post('/story_report/store', 'store')->name('story_report.store');
        Route::get('/story_report/edit/{id}', 'edit')->name('story_report.edit');
        Route::put('/story_report/update/{id}', 'update')->name('story_report.update');
        Route::delete('/story_report/destroy/{id}', 'destroy')->name('story_report.deleted');
        Route::post('/story_report/delete_all', 'destroy_all')->name('daily_report.destroy_all');
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

Route::middleware(['auth', 'role:developer', 'verified', 'profile.completed'])->prefix('authorization')->group(function () {
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles/list', 'index')->name('roles');
        Route::get('/roles/create', 'create')->name('roles.create');
        Route::post('/roles/store', 'store')->name('roles.store');
        Route::get('/roles/edit/{id}', 'edit')->name('roles.edit');
        Route::put('/roles/update/{id}', 'update')->name('roles.update');
        Route::delete('/roles/destroy/{id}', 'destroy')->name('roles.delete');
        Route::post('/roles/delete_all', 'destroyAll')->name('roles.destroy_all');
    });
    Route::controller(PermissionController::class)->group(function () {
        Route::get('/permissions/list', 'index')->name('permissions');
        Route::get('/permissions/create', 'create')->name('permissions.create');
        Route::post('/permissions/store', 'store')->name('permissions.store');
        Route::get('/permissions/edit/{id}', 'edit')->name('permissions.edit');
        Route::put('/permissions/update/{id}', 'update')->name('permissions.update');
        Route::delete('/permissions/destroy/{id}', 'destroy')->name('permissions.delete');
        Route::post('/permissions/delete_all', 'destroyAll')->name('permissions.destroy_all');
    });
    Route::controller(UsersController::class)->group(function () {
        Route::get('/users/list', 'index')->name('users');
        Route::get('/users/edit/{id}', 'edit')->name('users.edit');
        Route::put('/users/update/{id}', 'update')->name('users.update');
        Route::delete('/users/destroy/{id}', 'destroy')->name('users.delete');
        Route::post('/users/delete_all', 'destroyAll')->name('users.destroy_all');

        Route::get('/users/detail/{id}', 'detail')->name('users.detail');

        Route::get('/users/check/user', 'checkUser')->name('users.check');
        Route::get('/users/refresh', 'refresh')->name('users.refresh');
    });
});
