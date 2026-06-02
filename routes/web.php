<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/driving', [StudentController::class, 'index'])->name('dashboard');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::put('/students/{id}/payment', [StudentController::class, 'recordPayment'])->name('students.payment');
Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

Route::get('/test-design', function () {
    return view('test-design');
});



