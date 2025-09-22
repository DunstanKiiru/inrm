<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (!Schema::hasColumn('tpr_rules','cool_off_strategy')) $t->string('cool_off_strategy')->default('create_new')->after('auto_close_days'); // create_new|log_only|escalate_once|reopen_existing
            if (!Schema::hasColumn('tpr_rules','auto_reopen')) $t->boolean('auto_reopen')->default(true)->after('cool_off_strategy');
        });
    }
    public function down(): void {
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (Schema::hasColumn('tpr_rules','auto_reopen')) $t->dropColumn('auto_reopen');
            if (Schema::hasColumn('tpr_rules','cool_off_strategy')) $t->dropColumn('cool_off_strategy');
        });
    }
};
