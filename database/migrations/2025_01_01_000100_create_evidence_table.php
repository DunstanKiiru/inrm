<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('evidence', function(Blueprint $t){
  $t->id(); $t->string('entity_type'); $t->unsignedBigInteger('entity_id');
  $t->string('filename'); $t->string('storage_disk')->nullable(); $t->string('storage_path')->nullable();
  $t->unsignedBigInteger('size')->nullable(); $t->string('mime')->nullable(); $t->string('sha256',64)->index();
  $t->string('scanned_status')->default('unknown'); $t->foreignId('uploaded_by')->nullable()->constrained('users'); $t->timestamps(); }); }
  public function down(): void { Schema::dropIfExists('evidence'); } };