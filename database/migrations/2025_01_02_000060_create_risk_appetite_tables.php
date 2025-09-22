<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('risk_appetite_profiles', function(Blueprint $t){ $t->id(); $t->string('name'); $t->text('description')->nullable(); $t->timestamps(); });
    Schema::create('risk_thresholds', function(Blueprint $t){
      $t->id();
      $t->foreignId('profile_id')->constrained('risk_appetite_profiles')->cascadeOnDelete();
      $t->foreignId('category_id')->nullable()->constrained('risk_categories')->nullOnDelete();
      $t->string('owner_role')->nullable();
      $t->string('metric'); // inherent|residual
      $t->string('operator'); // <=,<,=,>=,>
      $t->decimal('limit',8,2);
      $t->string('band'); // Low|Medium|High|Extreme
      $t->string('color')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('risk_thresholds'); Schema::dropIfExists('risk_appetite_profiles'); }
};
