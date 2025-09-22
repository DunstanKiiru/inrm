<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RiskCategoryController;
use App\Http\Controllers\API\RiskCauseController;
use App\Http\Controllers\API\RiskConsequenceController;
use App\Http\Controllers\API\OrgUnitController;
use App\Http\Controllers\API\RiskTaxonomyController;
use App\Http\Controllers\API\RiskRollupController;
use App\Http\Controllers\API\RiskAppetiteController;
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


Route::middleware('auth:sanctum')->group(function(){
      // Dashboards
  Route::get('/dashboards', [DashboardController::class,'list']);
  Route::get('/dashboards/{dashboard}', [DashboardController::class,'show']);

  // KPIs
  Route::get('/kpis', [KpiController::class,'list']);
  Route::post('/kpis', [KpiController::class,'create']);
  Route::get('/kpis/{kpi}/series', [KpiController::class,'timeseries']);
  Route::post('/kpis/{kpi}/readings', [KpiController::class,'addReading']);

  // Exports
  Route::get('/export/board-pack/pdf', [ExportController::class,'boardPackPdf']);
  Route::get('/export/dashboard/csv', [ExportController::class,'dashboardCsv']);
  Route::get('/export/dashboard/xlsx', [ExportController::class,'dashboardXlsx']);

  // Digests
  Route::post('/digest/send-now', [DigestController::class,'sendNow']);

 // Plans
  Route::get('/audits/plans', [AuditPlanController::class,'index']);
  Route::post('/audits/plans', [AuditPlanController::class,'store']);
  Route::get('/audits/plans/{plan}', [AuditPlanController::class,'show']);
  Route::put('/audits/plans/{plan}', [AuditPlanController::class,'update']);

  // Procedures
  Route::post('/audits/plans/{plan}/procedures', [AuditPlanController::class,'addProcedure']);
  Route::put('/audits/plans/{plan}/procedures/{procedure}', [AuditPlanController::class,'updateProcedure']);

  // Samples
  Route::post('/audits/plans/{plan}/procedures/{procedure}/samples', [AuditPlanController::class,'addSample']);
  Route::post('/audits/plans/{plan}/procedures/{procedure}/samples/bulk', [AuditPlanController::class,'bulkSamples']);

  // Findings
  Route::post('/audits/plans/{plan}/findings', [AuditPlanController::class,'addFinding']);
  Route::put('/audits/plans/{plan}/findings/{finding}', [AuditPlanController::class,'updateFinding']);

  // Follow-ups
  Route::post('/audits/plans/{plan}/findings/{finding}/followups', [AuditPlanController::class,'addFollowUp']);

  // Taxonomy CRUD
  Route::apiResource('risk-categories', RiskCategoryController::class)->only(['index','store','update','destroy']);
  Route::apiResource('risk-causes', RiskCauseController::class)->only(['index','store','update','destroy']);
  Route::apiResource('risk-consequences', RiskConsequenceController::class)->only(['index','store','update','destroy']);
  Route::apiResource('org-units', OrgUnitController::class)->only(['index','store','update','destroy']);

  // Assign taxonomy to a risk
  Route::get('/risks/{risk}/taxonomy',[RiskTaxonomyController::class,'get']);
  Route::put('/risks/{risk}/taxonomy',[RiskTaxonomyController::class,'set']);

  // Rollups
  Route::get('/risks/rollups/category',[RiskRollupController::class,'byCategory']);
  Route::get('/risks/rollups/org-unit',[RiskRollupController::class,'byOrgUnit']);
  Route::get('/risks/rollups/owner',[RiskRollupController::class,'byOwner']);

  // Appetite & thresholds
  Route::get('/risk-appetite/profiles',[RiskAppetiteController::class,'profiles']);
  Route::post('/risk-appetite/profiles',[RiskAppetiteController::class,'storeProfile']);
  Route::get('/risk-appetite/profiles/{profile}/thresholds',[RiskAppetiteController::class,'thresholds']);
  Route::post('/risk-appetite/profiles/{profile}/thresholds',[RiskAppetiteController::class,'storeThreshold']);
  Route::get('/risks/{risk}/breaches',[RiskAppetiteController::class,'breaches']);

  Route::get('/controls',[ControlController::class,'index']);
  Route::post('/controls',[ControlController::class,'store']);
  Route::get('/controls/{control}',[ControlController::class,'show']);
  Route::put('/controls/{control}',[ControlController::class,'update']);
  Route::delete('/controls/{control}',[ControlController::class,'destroy']);
  // Control categories
  Route::get('/control-categories',[ControlController::class,'categories']);
  Route::post('/control-categories',[ControlController::class,'storeCategory']);

  // Risk mapping
  Route::post('/controls/{control}/map-risks',[ControlMappingController::class,'mapRisks']);
  Route::get('/controls/{control}/risks',[ControlMappingController::class,'risks']);

  // Test plans
  Route::get('/controls/{control}/test-plans',[ControlTestPlanController::class,'index']);
  Route::post('/controls/{control}/test-plans',[ControlTestPlanController::class,'store']);
  Route::put('/control-test-plans/{plan}',[ControlTestPlanController::class,'update']);
  Route::delete('/control-test-plans/{plan}',[ControlTestPlanController::class,'destroy']);

  // Executions
  Route::post('/control-test-plans/{plan}/execute',[ControlTestExecutionController::class,'execute']);
  Route::get('/control-test-executions/{execution}',[ControlTestExecutionController::class,'show']);

  // Issues & remediation
  Route::get('/control-issues',[ControlIssueController::class,'index']);
  Route::post('/control-issues',[ControlIssueController::class,'store']);
  Route::put('/control-issues/{controlIssue}',[ControlIssueController::class,'update']);
  Route::post('/control-issues/{controlIssue}/remediations',[ControlIssueController::class,'addRemediation']);

  Route::get('/controls/analytics/effectiveness-by-category',[ControlAnalyticsController::class,'effectivenessByCategory']);
  Route::get('/controls/analytics/effectiveness-by-owner',[ControlAnalyticsController::class,'effectivenessByOwner']);
  Route::get('/controls/analytics/passrate-series',[ControlAnalyticsController::class,'passrateSeries']);
  Route::get('/controls/analytics/owners',[ControlAnalyticsController::class,'owners']);
  Route::get('/controls/{control}/analytics/recent-executions',[ControlAnalyticsController::class,'recentExecutions']);
  // Templates
  Route::apiResource('assessment-templates', AssessmentTemplateController::class);
  // Assessments
  Route::get('/assessments', [AssessmentController::class,'index']);
  Route::post('/assessments', [AssessmentController::class,'store']);
  Route::get('/assessments/{assessment}', [AssessmentController::class,'show']);
  Route::put('/assessments/{assessment}', [AssessmentController::class,'update']);
  Route::delete('/assessments/{assessment}', [AssessmentController::class,'destroy']);
  Route::get('/assessments/{assessment}/rounds', [AssessmentController::class,'rounds']);
  Route::post('/assessment-rounds/{round}/submit', [AssessmentController::class,'submitResponse']);
  Route::get('/assessment-rounds/{round}/responses', [AssessmentController::class,'responses']);
  Route::put('/assessment-rounds/{round}/status', [AssessmentController::class,'setRoundStatus']);

  // KRIs
  Route::get('/kris', [KriController::class,'index']);
  Route::post('/kris', [KriController::class,'store']);
  Route::get('/kris/{kri}', [KriController::class,'show']);
  Route::put('/kris/{kri}', [KriController::class,'update']);
  Route::delete('/kris/{kri}', [KriController::class,'destroy']);
  Route::get('/kris/{kri}/readings', [KriController::class,'readings']);
  Route::post('/kris/{kri}/readings', [KriController::class,'addReading']);
  Route::get('/kris/{kri}/breaches', [KriController::class,'breaches']);

  Route::get('/kris/breaches/active', [KriBreachController::class,'active']);
  Route::post('/kris/breaches/{breach}/ack', [KriBreachController::class,'acknowledge']);
  Route::get('/kris/readings/batch', [KriReadingBatchController::class,'batch']);

});
