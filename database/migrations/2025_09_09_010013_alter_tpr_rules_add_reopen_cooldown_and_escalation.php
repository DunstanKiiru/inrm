<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (!Schema::hasColumn('tpr_rules','reopen_cooldown_hours')) $t->integer('reopen_cooldown_hours')->default(12)->after('auto_reopen');
            if (!Schema::hasColumn('tpr_rules','escalation_levels')) $t->json('escalation_levels')->nullable()->after('reopen_cooldown_hours');
        });
        Schema::table('tpr_rule_chains', function (Blueprint $t) {
            if (!Schema::hasColumn('tpr_rule_chains','retriggers')) $t->integer('retriggers')->default(0);
            if (!Schema::hasColumn('tpr_rule_chains','last_escalation')) $t->integer('last_escalation')->default(0);
            if (!Schema::hasColumn('tpr_rule_chains','last_trigger_at')) $t->timestamp('last_trigger_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('tpr_rule_chains', function (Blueprint $t) {
            if (Schema::hasColumn('tpr_rule_chains','last_trigger_at')) $t->dropColumn('last_trigger_at');
            if (Schema::hasColumn('tpr_rule_chains','last_escalation')) $t->dropColumn('last_escalation');
            if (Schema::hasColumn('tpr_rule_chains','retriggers')) $t->dropColumn('retriggers');
        });
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (Schema::hasColumn('tpr_rules','escalation_levels')) $t->dropColumn('escalation_levels');
            if (Schema::hasColumn('tpr_rules','reopen_cooldown_hours')) $t->dropColumn('reopen_cooldown_hours');
        });
    }
};
