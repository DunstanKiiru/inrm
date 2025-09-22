<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('workflow_automations')) {
            Schema::create('workflow_automations', function(Blueprint $t){
                $t->id(); $t->string('name'); $t->boolean('enabled')->default(true);
                $t->string('trigger_type',16)->default('SCHEDULE'); // SCHEDULE|RIM|TPR|INCIDENTS|CUSTOM
                $t->unsignedInteger('interval_minutes')->default(60); // for SCHEDULE
                $t->string('expression')->nullable(); // regex for event type or custom filter JSON
                $t->json('filter')->nullable(); // vendor tier, severity, etc
                $t->timestamp('last_run_at')->nullable();
                $t->timestamp('event_since_at')->nullable(); // poll window for event triggers
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('workflow_actions')) {
            Schema::create('workflow_actions', function(Blueprint $t){
                $t->id(); $t->unsignedBigInteger('automation_id');
                $t->unsignedInteger('order')->default(0);
                $t->string('type',32); // webhook_post|create_incident|emit_rim|run_tpr|snapshot_boardpack|notify_mail
                $t->json('config')->nullable();
                $t->timestamps(); $t->index(['automation_id','order']);
            });
        }
        if (!Schema::hasTable('workflow_runs')) {
            Schema::create('workflow_runs', function(Blueprint $t){
                $t->id(); $t->unsignedBigInteger('automation_id'); $t->timestamp('started_at'); $t->timestamp('finished_at')->nullable();
                $t->string('status',16)->default('running'); // success|failed|skipped|running
                $t->json('meta')->nullable();
                $t->timestamps(); $t->index(['automation_id','started_at']);
            });
        }
        if (!Schema::hasTable('workflow_logs')) {
            Schema::create('workflow_logs', function(Blueprint $t){
                $t->id(); $t->unsignedBigInteger('run_id'); $t->string('level',8)->default('info'); $t->text('message'); $t->json('context')->nullable();
                $t->timestamps(); $t->index(['run_id']);
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('workflow_logs'); Schema::dropIfExists('workflow_runs'); Schema::dropIfExists('workflow_actions'); Schema::dropIfExists('workflow_automations');
    }
};
