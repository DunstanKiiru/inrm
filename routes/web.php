<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SSOController;
use App\Http\Controllers\API\ReportController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/auth/azure/redirect',[SSOController::class,'redirectToAzure'])->name('sso.azure.redirect');
Route::get('/auth/azure/callback',[SSOController::class,'handleAzureCallback'])->name('sso.azure.callback');
Route::get('/reports/boardpack/preview',[ReportController::class,'boardpackPreview'])->name('boardpack.preview');

// Fallback route for undefined routes
Route::fallback(function () {
    return redirect()->route('home');
});
