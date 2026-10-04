<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

// Public reviews consumed by the main website (newyorkdrivingschool.academy)
Route::get('/reviews/public', [ReviewController::class, 'publicIndex'])
    ->middleware('throttle:60,1')
    ->name('api.reviews.public');
