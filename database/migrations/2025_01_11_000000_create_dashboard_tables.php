<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('kpis', function(Blueprint $t){
      $t->id(); $t->string('key')->unique(); $t->string('title');
      $t->string('unit')->nullable(); $t->decimal('target',12,2)->nullable(); $t->string('direction')->default('down');
      $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $t->timestamps();
    });
    Schema::create('kpi_readings', function(Blueprint $t){
      $t->id(); $t->foreignId('kpi_id')->constrained('kpis')->cascadeOnDelete();
      $t->timestamp('ts'); $t->decimal('value',12,2); $t->timestamps();
      $t->index(['kpi_id','ts']);
    });
    Schema::create('dashboards', function(Blueprint $t){
      $t->id(); $t->string('slug')->unique(); $t->string('title'); $t->string('role')->nullable();
      $t->json('layout_json')->nullable(); $t->boolean('is_default')->default(false); $t->timestamps();
    });
    Schema::create('report_widgets', function(Blueprint $t){
      $t->id(); $t->foreignId('dashboard_id')->constrained('dashboards')->cascadeOnDelete();
      $t->string('type'); $t->string('title'); $t->json('config_json')->nullable(); $t->unsignedInteger('order_index')->default(0); $t->timestamps();
    });
    Schema::create('board_packs', function(Blueprint $t){
      $t->id(); $t->string('title'); $t->foreignId('dashboard_id')->nullable()->constrained('dashboards')->nullOnDelete();
      $t->date('from_date')->nullable(); $t->date('to_date')->nullable(); $t->json('filters_json')->nullable(); $t->string('status')->default('ready'); $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('board_packs');
    Schema::dropIfExists('report_widgets');
    Schema::dropIfExists('dashboards');
    Schema::dropIfExists('kpi_readings');
    Schema::dropIfExists('kpis');
  }
};
