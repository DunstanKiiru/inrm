<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ControlController;
use App\Http\Controllers\API\ControlMappingController;
use App\Http\Controllers\API\ControlTestPlanController;
use App\Http\Controllers\API\ControlTestExecutionController;
use App\Http\Controllers\API\ControlIssueController;
use App\Http\Controllers\API\ControlAnalyticsController;
use App\Http\Controllers\API\AssessmentTemplateController;
use App\Http\Controllers\API\AssessmentController;
use App\Http\Controllers\API\KriController;
use App\Http\Controllers\API\KriBreachController;
use App\Http\Controllers\API\KriReadingBatchController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\KpiController;
use App\Http\Controllers\API\ExportController;
use App\Http\Controllers\API\DigestController;
use App\Http\Controllers\API\AuditPlanController;
use App\Http\Controllers\API\FrameworkController;
use App\Http\Controllers\API\ObligationController;
use App\Http\Controllers\API\PolicyController;
use App\Http\Controllers\API\AttestationController;

// ----------------------
// CORS preflight
// ----------------------
Route::options('{any}', function () {
    return response('', 204)
        ->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ]);
})->where('any', '.*');

// ----------------------
// Public routes
// ----------------------
Route::get('/ping', function () {
    return response()->json(['pong' => true])
        ->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ]);
});

// ----------------------
// Auth routes
// ----------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    // ----------------------
    // Dashboards
    // ----------------------
    Route::get('/dashboards', [DashboardController::class, 'list']);
    Route::get('/dashboards/{dashboard}', [DashboardController::class, 'show']);

    // ----------------------
    // KPIs
    // ----------------------
    Route::get('/kpis', [KpiController::class, 'list']);
    Route::post('/kpis', [KpiController::class, 'create']);
    Route::get('/kpis/{kpi}/series', [KpiController::class, 'timeseries']);
    Route::post('/kpis/{kpi}/readings', [KpiController::class, 'addReading']);

    // ----------------------
    // Exports
    // ----------------------
    Route::get('/export/board-pack/pdf', [ExportController::class, 'boardPackPdf']);
    Route::get('/export/dashboard/csv', [ExportController::class, 'dashboardCsv']);
    Route::get('/export/dashboard/xlsx', [ExportController::class, 'dashboardXlsx']);

    // ----------------------
    // Digests
    // ----------------------
    Route::post('/digest/send-now', [DigestController::class, 'sendNow']);

    // ----------------------
    // Audit Plans
    // ----------------------
    Route::prefix('audits/plans')->group(function () {
        Route::get('/', [AuditPlanController::class, 'index']);
        Route::post('/', [AuditPlanController::class, 'store']);
        Route::get('{plan}', [AuditPlanController::class, 'show']);
        Route::put('{plan}', [AuditPlanController::class, 'update']);

        Route::post('{plan}/procedures', [AuditPlanController::class, 'addProcedure']);
        Route::put('{plan}/procedures/{procedure}', [AuditPlanController::class, 'updateProcedure']);

        Route::post('{plan}/procedures/{procedure}/samples', [AuditPlanController::class, 'addSample']);
        Route::post('{plan}/procedures/{procedure}/samples/bulk', [AuditPlanController::class, 'bulkSamples']);

        Route::post('{plan}/findings', [AuditPlanController::class, 'addFinding']);
        Route::put('{plan}/findings/{finding}', [AuditPlanController::class, 'updateFinding']);

        Route::post('{plan}/findings/{finding}/followups', [AuditPlanController::class, 'addFollowUp']);
    });

    // ----------------------
    // Controls
    // ----------------------
    Route::get('/controls', [ControlController::class, 'index']);
    Route::post('/controls', [ControlController::class, 'store']);
    Route::get('/controls/{control}', [ControlController::class, 'show']);
    Route::put('/controls/{control}', [ControlController::class, 'update']);
    Route::delete('/controls/{control}', [ControlController::class, 'destroy']);

    Route::get('/control-categories', [ControlController::class, 'categories']);
    Route::post('/control-categories', [ControlController::class, 'storeCategory']);

    // Risk mapping
    Route::post('/controls/{control}/map-risks', [ControlMappingController::class, 'mapRisks']);
    Route::get('/controls/{control}/risks', [ControlMappingController::class, 'risks']);

    // Test Plans & Executions
    Route::get('/controls/{control}/test-plans', [ControlTestPlanController::class, 'index']);
    Route::post('/controls/{control}/test-plans', [ControlTestPlanController::class, 'store']);
    Route::put('/control-test-plans/{plan}', [ControlTestPlanController::class, 'update']);
    Route::delete('/control-test-plans/{plan}', [ControlTestPlanController::class, 'destroy']);

    Route::post('/control-test-plans/{plan}/execute', [ControlTestExecutionController::class, 'execute']);
    Route::get('/control-test-executions/{execution}', [ControlTestExecutionController::class, 'show']);

    // Issues
    Route::get('/control-issues', [ControlIssueController::class, 'index']);
    Route::post('/control-issues', [ControlIssueController::class, 'store']);
    Route::put('/control-issues/{controlIssue}', [ControlIssueController::class, 'update']);
    Route::post('/control-issues/{controlIssue}/remediations', [ControlIssueController::class, 'addRemediation']);

    // Analytics
    Route::get('/controls/analytics/effectiveness-by-category', [ControlAnalyticsController::class, 'effectivenessByCategory']);
    Route::get('/controls/analytics/effectiveness-by-owner', [ControlAnalyticsController::class, 'effectivenessByOwner']);
    Route::get('/controls/analytics/passrate-series', [ControlAnalyticsController::class, 'passrateSeries']);
    Route::get('/controls/analytics/owners', [ControlAnalyticsController::class, 'owners']);
    Route::get('/controls/{control}/analytics/recent-executions', [ControlAnalyticsController::class, 'recentExecutions']);

    // Assessment templates & assessments
    Route::apiResource('assessment-templates', AssessmentTemplateController::class);
    Route::apiResource('assessments', AssessmentController::class);

    Route::get('/assessment-rounds/{round}/responses', [AssessmentController::class, 'responses']);
    Route::post('/assessment-rounds/{round}/submit', [AssessmentController::class, 'submitResponse']);
    Route::put('/assessment-rounds/{round}/status', [AssessmentController::class, 'setRoundStatus']);

    // KRIs
    Route::get('/kris', [KriController::class, 'index']);
    Route::post('/kris', [KriController::class, 'store']);
    Route::get('/kris/{kri}', [KriController::class, 'show']);
    Route::put('/kris/{kri}', [KriController::class, 'update']);
    Route::delete('/kris/{kri}', [KriController::class, 'destroy']);
    Route::get('/kris/{kri}/readings', [KriController::class, 'readings']);
    Route::post('/kris/{kri}/readings', [KriController::class, 'addReading']);
    Route::get('/kris/{kri}/breaches', [KriController::class, 'breaches']);

     Route::get('/kris/breaches/active', [KriBreachController::class,'active']);
    Route::post('/kris/breaches/{breach}/ack', [KriBreachController::class,'acknowledge']);

    // Frameworks
  Route::get('/frameworks', [FrameworkController::class,'index']);
  Route::get('/frameworks/{framework}', [FrameworkController::class,'show']);
  Route::post('/frameworks/{framework}/requirements/{requirement}/map-control', [FrameworkController::class,'mapControl']);
  Route::delete('/frameworks/{framework}/requirements/{requirement}/map-control/{controlId}', [FrameworkController::class,'unmapControl']);

  // Obligations
  Route::get('/obligations', [ObligationController::class,'index']);
  Route::post('/obligations', [ObligationController::class,'store']);
  Route::put('/obligations/{obligation}', [ObligationController::class,'update']);
  Route::delete('/obligations/{obligation}', [ObligationController::class,'destroy']);

  // Policies
  Route::get('/policies', [PolicyController::class,'index']);
  Route::post('/policies', [PolicyController::class,'store']);
  Route::get('/policies/{policy}', [PolicyController::class,'show']);
  Route::put('/policies/{policy}', [PolicyController::class,'update']);
  Route::post('/policies/{policy}/versions', [PolicyController::class,'addVersion']);
  Route::post('/policies/{policy}/transition', [PolicyController::class,'transition']);

  // Attestations
  Route::get('/my-attestations', [AttestationController::class,'myList']);
  Route::post('/policies/{policy}/attest', [AttestationController::class,'attest']);


});
