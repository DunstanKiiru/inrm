<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('assessment_templates', function(Blueprint $t){
      $t->id(); $t->string('title'); $t->text('description')->nullable();
      $t->string('entity_type')->default('risk'); // risk|org_unit
      $t->json('schema_json'); $t->json('ui_schema_json')->nullable();
      $t->string('status')->default('active'); // draft|active|retired
      $t->timestamps();
    });
    Schema::create('assessments', function(Blueprint $t){
      $t->id(); $t->foreignId('template_id')->constrained('assessment_templates')->cascadeOnDelete();
      $t->string('entity_type'); $t->unsignedBigInteger('entity_id');
      $t->string('title'); $t->string('status')->default('open'); // open|closed|archived
      $t->timestamps();
    });
    Schema::create('assessment_rounds', function(Blueprint $t){
      $t->id(); $t->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
      $t->unsignedInteger('round_no'); $t->dateTime('due_at')->nullable();
      $t->string('status')->default('pending'); // pending|in-progress|submitted|approved|rejected|closed
      $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
      $t->timestamps();
    });
    Schema::create('assessment_responses', function(Blueprint $t){
      $t->id(); $t->foreignId('round_id')->constrained('assessment_rounds')->cascadeOnDelete();
      $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
      $t->json('answers_json'); $t->string('status')->default('submitted'); // submitted|revised|withdrawn
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('assessment_responses');
    Schema::dropIfExists('assessment_rounds');
    Schema::dropIfExists('assessments');
    Schema::dropIfExists('assessment_templates');
  }
};
