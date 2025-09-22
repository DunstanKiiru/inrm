<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('policies', function(Blueprint $t){
      $t->id();
      $t->string('title');
      $t->string('status')->default('draft'); // draft|review|approve|publish|retired
      $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
      $t->date('effective_date')->nullable();
      $t->date('review_date')->nullable();
      $t->boolean('require_attestation')->default(true);
      $t->timestamps();
    });
    Schema::create('policy_versions', function(Blueprint $t){
      $t->id();
      $t->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
      $t->unsignedInteger('version')->default(1);
      $t->longText('body_html')->nullable();
      $t->text('notes')->nullable();
      $t->timestamps();
      $t->unique(['policy_id','version']);
    });
    Schema::create('policy_attestations', function(Blueprint $t){
      $t->id();
      $t->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
      $t->foreignId('policy_version_id')->nullable()->constrained('policy_versions')->nullOnDelete();
      $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $t->timestamp('attested_at')->nullable();
      $t->timestamps();
      $t->unique(['policy_id','user_id','policy_version_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('policy_attestations');
    Schema::dropIfExists('policy_versions');
    Schema::dropIfExists('policies');
  }
};
