<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UploadController;

// ---------- Authentication ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Admin panel (requires a logged-in user) ----------
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/driving', [StudentController::class, 'index'])->name('dashboard');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{id}/payment', [StudentController::class, 'recordPayment'])->name('students.payment');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');

    // Student Payments
    Route::post('/payments', [StudentController::class, 'storePayment'])->name('payments.store');
    Route::get('/payments/{id}/receipt', [StudentController::class, 'printReceipt'])->name('payments.receipt');

    Route::post('/instructors', [InstructorController::class, 'store'])->name('instructors.store');
    Route::delete('/instructors/{id}', [InstructorController::class, 'destroy'])->name('instructors.destroy');
    Route::put('/instructors/{id}', [InstructorController::class, 'update'])->name('instructors.update');

    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])->name('vehicles.update');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{id}/visibility', [ReviewController::class, 'toggleVisibility'])->name('reviews.visibility');

    // Student / instructor photos and licenses (private files)
    Route::get('/uploads/{folder}/{file}', [UploadController::class, 'show'])
        ->where(['folder' => 'students|instructors|licenses', 'file' => '[A-Za-z0-9_\-]+\.(jpe?g|png|gif|webp)'])
        ->name('uploads.show');

    Route::get('/test-design', function () {
        return view('test-design');
    });
});
