<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Business Processes
        Schema::create('business_processes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // BCM Risks
        Schema::create('bcm_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_process_id')->constrained('business_processes')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('impact_score')->nullable();
            $table->integer('likelihood_score')->nullable();
            $table->timestamps();
        });

        // BCM Controls
        Schema::create('bcm_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bcm_risk_id')->constrained('bcm_risks')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Recovery Plans
        Schema::create('recovery_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_process_id')->constrained('business_processes')->cascadeOnDelete();
            $table->string('name');
            $table->text('steps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_plans');
        Schema::dropIfExists('bcm_controls');
        Schema::dropIfExists('bcm_risks');
        Schema::dropIfExists('business_processes');
    }
};
