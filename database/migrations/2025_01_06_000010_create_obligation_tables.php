<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('obligations', function(Blueprint $t){
      $t->id();
      $t->string('title');
      $t->string('jurisdiction')->nullable();
      $t->string('source_doc_url')->nullable();
      $t->text('summary')->nullable();
      $t->date('effective_date')->nullable();
      $t->string('review_cycle')->nullable(); // e.g., annual, biennial
      $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $t->timestamps();
    });
    Schema::create('obligation_requirement', function(Blueprint $t){
      $t->id();
      $t->foreignId('obligation_id')->constrained('obligations')->cascadeOnDelete();
      $t->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
      $t->timestamps();
      $t->unique(['obligation_id','requirement_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('obligation_requirement');
    Schema::dropIfExists('obligations');
  }
};
