<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('risk_categories', function(Blueprint $t){
      $t->id(); $t->string('name'); $t->unsignedBigInteger('parent_id')->nullable(); $t->timestamps();
      $t->foreign('parent_id')->references('id')->on('risk_categories')->nullOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('risk_categories'); }
};
