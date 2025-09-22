<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('cause_risk', function(Blueprint $t){
      $t->id(); $t->foreignId('risk_id')->constrained('risks')->cascadeOnDelete(); $t->foreignId('risk_cause_id')->constrained('risk_causes')->cascadeOnDelete(); $t->unique(['risk_id','risk_cause_id']);
    });
    Schema::create('consequence_risk', function(Blueprint $t){
      $t->id(); $t->foreignId('risk_id')->constrained('risks')->cascadeOnDelete(); $t->foreignId('risk_consequence_id')->constrained('risk_consequences')->cascadeOnDelete(); $t->unique(['risk_id','risk_consequence_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('consequence_risk'); Schema::dropIfExists('cause_risk'); }
};
