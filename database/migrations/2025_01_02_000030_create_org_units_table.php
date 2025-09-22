<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('org_units', function(Blueprint $t){
      $t->id(); $t->string('name'); $t->unsignedBigInteger('parent_id')->nullable(); $t->timestamps();
      $t->foreign('parent_id')->references('id')->on('org_units')->nullOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('org_units'); }
};
