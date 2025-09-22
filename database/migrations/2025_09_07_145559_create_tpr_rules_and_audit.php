<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tpr_rules', function (Blueprint $t) {
            $t->id();
            $t->string('type'); // KRI_ALERTS_IN_WINDOW | SLA_BREACHES_IN_WINDOW
            $t->string('metric')->nullable(); // kri | sla
            $t->string('code_pattern')->nullable(); // e.g., '^AVAIL' or exact 'AVAIL_99' (PCRE)
            $t->integer('window_days')->default(30);
            $t->integer('threshold')->default(3);
            $t->string('scope')->nullable(); // JSON: {vendor_id?, tier?, category?}
            $t->boolean('enabled')->default(true);
            $t->string('action')->default('create_issue'); // create_issue | rim_event_only | none
            $t->string('issue_priority')->nullable(); // high|medium|low
            $t->string('title_template')->nullable(); // e.g., "[TPR] {{metric}} threshold for {{vendor_code}}"
            $t->text('description_template')->nullable();
            $t->timestamps();
        });

        Schema::create('tpr_rule_audit', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rule_id')->constrained('tpr_rules');
            $t->foreignId('vendor_id')->nullable();
            $t->string('vendor_code')->nullable();
            $t->string('metric')->nullable(); // kri|sla
            $t->string('matched_code')->nullable();
            $t->integer('count')->default(0);
            $t->date('window_start')->nullable();
            $t->date('window_end')->nullable();
            $t->string('action_taken')->nullable(); // created_issue|rim_event|noop|skipped
            $t->foreignId('issue_id')->nullable();
            $t->json('payload')->nullable(); // extra context
            $t->timestamp('triggered_at')->useCurrent();
            $t->timestamps();
            $t->unique(['rule_id','vendor_id','matched_code','window_end'], 'tpr_rule_audit_uniq');
        });
    }
    public function down(): void {
        Schema::dropIfExists('tpr_rule_audit');
        Schema::dropIfExists('tpr_rules');
    }
};
