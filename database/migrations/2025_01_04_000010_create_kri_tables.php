<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('kris', function(Blueprint $t){
      $t->id();
      $t->string('title'); $t->text('description')->nullable();
      $t->string('entity_type'); $t->unsignedBigInteger('entity_id');
      $t->string('unit')->nullable();
      $t->string('cadence')->default('monthly'); // weekly|monthly|quarterly
      $t->decimal('target',12,4)->nullable();
      $t->decimal('warn_threshold',12,4)->nullable();
      $t->decimal('alert_threshold',12,4)->nullable();
      $t->string('direction')->default('lower_is_better'); // or higher_is_better
      $t->timestamps();
    });
    Schema::create('kri_readings', function(Blueprint $t){
      $t->id(); $t->foreignId('kri_id')->constrained('kris')->cascadeOnDelete();
      $t->decimal('value',14,4); $t->dateTime('collected_at')->nullable(); $t->string('source')->nullable();
      $t->timestamps();
    });
    Schema::create('kri_breaches', function(Blueprint $t){
      $t->id(); $t->foreignId('kri_id')->constrained('kris')->cascadeOnDelete();
      $t->foreignId('reading_id')->nullable()->constrained('kri_readings')->nullOnDelete();
      $t->string('level'); // warn|alert
      $t->string('message')->nullable();
      $t->dateTime('acknowledged_at')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('kri_breaches'); Schema::dropIfExists('kri_readings'); Schema::dropIfExists('kris');
  }
};
