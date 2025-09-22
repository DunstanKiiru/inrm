<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('control_test_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('control_id')->constrained('controls')->cascadeOnDelete();
            $t->string('test_type'); // design | operating
            $t->string('frequency'); // monthly | quarterly | annual | ad-hoc
            $t->dateTime('next_due')->nullable();
            $t->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $t->string('status')->default('active'); // active | paused | retired
            $t->text('scope')->nullable();
            $t->text('methodology')->nullable();
            $t->timestamps();
        });

        Schema::create('control_test_executions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('plan_id')
                ->constrained('control_test_plans')
                ->cascadeOnDelete();
            $t->dateTime('executed_at')->nullable();
            $t->foreignId('executed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $t->string('result')->nullable(); // pass | fail | partial
            $t->string('effectiveness_rating')->nullable(); // Effective | Partial | Ineffective
            $t->text('comments')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_test_executions');
        Schema::dropIfExists('control_test_plans');
    }
};
