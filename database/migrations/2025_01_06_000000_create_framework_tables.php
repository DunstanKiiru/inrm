<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('frameworks', function(Blueprint $t){
      $t->id();
      $t->string('key')->unique();
      $t->string('title');
      $t->text('description')->nullable();
      $t->string('version')->nullable();
      $t->timestamps();
    });
    Schema::create('requirements', function(Blueprint $t){
      $t->id();
      $t->foreignId('framework_id')->constrained('frameworks')->cascadeOnDelete();
      $t->foreignId('parent_id')->nullable()->constrained('requirements')->nullOnDelete();
      $t->string('code')->nullable();
      $t->string('title');
      $t->text('description')->nullable();
      $t->timestamps();
      $t->index(['framework_id','code']);
    });
    Schema::create('control_requirement', function(Blueprint $t){
      $t->id();
      $t->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
      $t->foreignId('control_id')->constrained('controls')->cascadeOnDelete();
      $t->timestamps();
      $t->unique(['requirement_id','control_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('control_requirement');
    Schema::dropIfExists('requirements');
    Schema::dropIfExists('frameworks');
  }
};
