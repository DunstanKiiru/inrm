<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SSOController;
use App\Http\Controllers\API\ReportController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/reports/boardpack/preview',[ReportController::class,'boardpackPreview'])->name('boardpack.preview');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');


