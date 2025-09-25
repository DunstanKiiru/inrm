<?php
use Illuminate\Support\Facades\Route;
use Inrm\Workflow\Http\Controllers\AutomationsController;

Route::prefix('workflow')->group(function(){
    Route::get('/', [AutomationsController::class,'index']);
    Route::get('/create', [AutomationsController::class,'create']);
    Route::post('/', [AutomationsController::class,'store']);
    Route::get('/{id}', [AutomationsController::class,'show']);
    Route::post('/{id}/run', [AutomationsController::class,'run']);
    Route::post('/{id}/enable', [AutomationsController::class,'enable']);
    Route::post('/{id}/disable', [AutomationsController::class,'disable']);
});
