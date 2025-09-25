<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes for browser + SPA.
| Keep POST /login before the catch-all so it isn’t shadowed.
|
*/

// ===== Authentication =====
Route::post('/login', [LoginController::class, 'login'])->name('login');

// ===== Reports =====
Route::get('/reports/boardpack/preview', [ReportController::class, 'boardpackPreview'])
    ->name('boardpack.preview');

// ===== Workflow React =====
Route::get('/workflow', function () {
    return view('workflow');
});

// ===== SPA Catch-all =====
// Handles everything except /api/*
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
