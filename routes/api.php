<?php

use App\Http\Controllers\categoriesController;
use App\Http\Controllers\examsController;
use App\Http\Controllers\questionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->apiResource('categories', categoriesController::class);
Route::middleware(['web', 'auth'])->apiResource('exams', examsController::class);
Route::middleware(['web', 'auth'])->apiResource('questions', questionsController::class);
