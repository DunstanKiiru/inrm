<?php
use Illuminate\Support\Facades\Route;
use Inrm\Workflow\Http\Controllers\Api\WorkflowCrudApi;

Route::get('/workflow/automations', [WorkflowCrudApi::class,'list']);
Route::get('/workflow/automations/{id}', [WorkflowCrudApi::class,'detail']);
Route::post('/workflow/automations', [WorkflowCrudApi::class,'create']);
Route::post('/workflow/automations/{id}/toggle', [WorkflowCrudApi::class,'toggle']);
Route::post('/workflow/automations/{id}/run', [WorkflowCrudApi::class,'run']);
Route::post('/workflow/run-due', [WorkflowCrudApi::class,'runDue']);
