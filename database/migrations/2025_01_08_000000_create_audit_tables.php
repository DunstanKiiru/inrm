<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('audit_plans', function(Blueprint $t){
      $t->id();
      $t->string('ref')->unique();
      $t->string('title');
      $t->text('scope')->nullable();
      $t->date('period_start')->nullable();
      $t->date('period_end')->nullable();
      $t->string('status')->default('planned'); // planned|fieldwork|reporting|follow_up|closed
      $t->foreignId('lead_id')->nullable()->constrained('users')->nullOnDelete();
      $t->json('team_json')->nullable();
      $t->text('objectives')->nullable();
      $t->text('methodology')->nullable();
      $t->timestamps();
    });
    Schema::create('audit_procedures', function(Blueprint $t){
      $t->id();
      $t->foreignId('audit_plan_id')->constrained('audit_plans')->cascadeOnDelete();
      $t->string('ref')->nullable();
      $t->string('title');
      $t->text('description')->nullable();
      $t->string('status')->default('open'); // open|testing|complete
      $t->foreignId('tester_id')->nullable()->constrained('users')->nullOnDelete();
      $t->unsignedInteger('population_size')->nullable();
      $t->string('sample_method')->nullable(); // random|judgmental|systematic
      $t->unsignedInteger('sample_size')->nullable();
      $t->text('results_summary')->nullable();
      $t->timestamps();
      $t->index(['audit_plan_id','status']);
    });
    Schema::create('audit_samples', function(Blueprint $t){
      $t->id();
      $t->foreignId('audit_procedure_id')->constrained('audit_procedures')->cascadeOnDelete();
      $t->unsignedInteger('sample_no');
      $t->string('population_ref')->nullable();
      $t->json('attributes_json')->nullable();
      $t->timestamp('tested_at')->nullable();
      $t->string('result')->nullable(); // pass|fail|exception
      $t->text('notes')->nullable();
      $t->timestamps();
      $t->unique(['audit_procedure_id','sample_no']);
    });
    Schema::create('audit_findings', function(Blueprint $t){
      $t->id();
      $t->foreignId('audit_plan_id')->constrained('audit_plans')->cascadeOnDelete();
      $t->foreignId('audit_procedure_id')->nullable()->constrained('audit_procedures')->nullOnDelete();
      $t->string('title');
      $t->text('description')->nullable();
      $t->string('severity')->default('medium'); // low|medium|high|critical
      $t->string('rating')->nullable(); // design|operating
      $t->text('cause')->nullable();
      $t->text('impact')->nullable();
      $t->text('criteria')->nullable();
      $t->text('condition')->nullable();
      $t->text('recommendation')->nullable();
      $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $t->date('target_date')->nullable();
      $t->string('status')->default('open'); // open|remediated|closed
      $t->unsignedBigInteger('risk_id')->nullable(); // link to risk register id if present
      $t->timestamps();
      $t->index(['audit_plan_id','status','severity']);
    });
    Schema::create('audit_follow_ups', function(Blueprint $t){
      $t->id();
      $t->foreignId('finding_id')->constrained('audit_findings')->cascadeOnDelete();
      $t->timestamp('test_date')->nullable();
      $t->string('result')->nullable(); // pass|fail
      $t->text('notes')->nullable();
      $t->foreignId('tester_id')->nullable()->constrained('users')->nullOnDelete();
      $t->string('evidence_url')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('audit_follow_ups');
    Schema::dropIfExists('audit_findings');
    Schema::dropIfExists('audit_samples');
    Schema::dropIfExists('audit_procedures');
    Schema::dropIfExists('audit_plans');
  }
};
