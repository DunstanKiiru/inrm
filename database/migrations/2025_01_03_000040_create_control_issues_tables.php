<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('control_issues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('control_id')
                ->constrained('controls')
                ->cascadeOnDelete();

            $t->foreignId('test_execution_id')
                ->nullable()
                ->constrained('control_test_executions')
                ->nullOnDelete();

            $t->text('description');
            $t->string('severity')->default('Medium'); // Low | Medium | High | Critical
            $t->string('status')->default('open');     // open | in-progress | closed

            $t->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $t->dateTime('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('control_remediations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('issue_id')
                ->constrained('control_issues')
                ->cascadeOnDelete();

            $t->text('description');
            $t->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $t->dateTime('due_date')->nullable();
            $t->string('status')->default('open'); // open | in-progress | done
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_remediations');
        Schema::dropIfExists('control_issues');
    }
};
