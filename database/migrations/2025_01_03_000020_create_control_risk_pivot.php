<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('control_risk')) {
            Schema::create('control_risk', function (Blueprint $t) {
                $t->id();
                $t->foreignId('control_id')
                    ->constrained('controls')
                    ->cascadeOnDelete();
                $t->foreignId('risk_id')
                    ->constrained('risks')
                    ->cascadeOnDelete();
                $t->string('effectiveness_rating')->nullable(); // Effective / Partial / Ineffective
                $t->decimal('residual_impact', 8, 2)->nullable();
                $t->unique(['control_id', 'risk_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('control_risk');
    }
};
