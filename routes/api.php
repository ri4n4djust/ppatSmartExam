<?php

use App\Http\Controllers\categoriesController;
use App\Http\Controllers\examsController;
use App\Http\Controllers\questionsController;
use App\Http\Controllers\laporanController;
use Illuminate\Support\Facades\Route;

// Route::middleware(['web', 'auth'])->apiResource('categories', categoriesController::class);
// Route::middleware(['web', 'auth'])->apiResource('exams', examsController::class);

Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('questions', QuestionsController::class);
    Route::apiResource('exams', examsController::class);
    Route::apiResource('categories', categoriesController::class);

    // custom route tambahan
    Route::post('questions/assign', [QuestionsController::class, 'assignQuestions']);
    Route::post('exams/submit', [ExamsController::class, 'submitExam']);
    Route::post('hasil-ujian', [ExamsController::class, 'getExamResults']);
    Route::post('cek-ujian', [ExamsController::class, 'checkExamStatus']);

    Route::post('laporan-user', [laporanController::class, 'indexUser']);
    Route::post('laporan-admin', [laporanController::class, 'indexAdmin']);

    Route::post('daftar-siswa', [laporanController::class, 'indexSiswa']);
    Route::put('daftar-siswa/{siswa}', [laporanController::class, 'updateSiswa']);
    Route::post('daftar-pengguna', [laporanController::class, 'indexPengguna']);
});
