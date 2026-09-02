<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/csrf-token', fn () => response()->json([
    'token' => csrf_token(),
]));
Route::get('/auth/user', fn (Request $request) => response()->json([
    'user' => $request->user(),
]))->middleware('auth');

Route::view('/login', 'application')->name('login');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'application');
    Route::view('/account-settings', 'application');
    Route::view('/bank-soal', 'application');
    Route::view('/typography', 'application');
    Route::view('/icons', 'application');
    Route::view('/cards', 'application');
    Route::view('/tables', 'application');
    Route::view('/form-layouts', 'application');
});

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');
