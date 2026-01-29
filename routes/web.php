<?php

use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VoteController::class, 'index']);
Route::post('/vote', [VoteController::class, 'store']);
Route::get('/api/results', [VoteController::class, 'results']);
