<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('tpr_rule_chains')) {
            Schema::create('tpr_rule_chains', function (Blueprint $t) {
                $t->id();
                $t->foreignId('rule_id')->constrained('tpr_rules');
                $t->foreignId('vendor_id');
                $t->string('vendor_code');
                $t->string('metric');      // kri|sla|composite
                $t->string('matched_code'); // e.g., S1 or AVAIL_99 or COMPOSITE
                $t->foreignId('issue_id')->nullable();
                $t->string('status')->default('open'); // open|closed
                $t->timestamp('opened_at')->nullable();
                $t->timestamp('closed_at')->nullable();
                $t->timestamps();
                $t->unique(['rule_id','vendor_id','metric','matched_code'], 'tpr_rule_chains_uniq');
            });
        }
        // Add chain_id to audit (nullable)
        Schema::table('tpr_rule_audit', function (Blueprint $t) {
            if (!Schema::hasColumn('tpr_rule_audit','chain_id')) $t->foreignId('chain_id')->nullable()->after('issue_id');
        });
    }
    public function down(): void {
        Schema::table('tpr_rule_audit', function (Blueprint $t) {
            if (Schema::hasColumn('tpr_rule_audit','chain_id')) $t->dropColumn('chain_id');
        });
        Schema::dropIfExists('tpr_rule_chains');
    }
};
