<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('risk_categories')->nullOnDelete();
            $table->foreignId('org_unit_id')->nullable()->constrained('org_units')->nullOnDelete();

            $table->unsignedTinyInteger('likelihood');
            $table->unsignedTinyInteger('impact');
            $table->decimal('weight', 4, 2)->default(1.0);
            $table->decimal('inherent_score', 8, 2)->nullable();
            $table->decimal('residual_score', 8, 2)->nullable();

            $table->string('status')->default('active');
            $table->json('custom_json')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
