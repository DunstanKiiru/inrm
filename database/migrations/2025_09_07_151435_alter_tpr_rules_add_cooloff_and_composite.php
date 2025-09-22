<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (!Schema::hasColumn('tpr_rules','cool_off_days')) $t->integer('cool_off_days')->default(14)->after('threshold');
            if (!Schema::hasColumn('tpr_rules','auto_close_days')) $t->integer('auto_close_days')->default(7)->after('cool_off_days');
            if (!Schema::hasColumn('tpr_rules','logic_type')) $t->string('logic_type')->default('SIMPLE')->after('action'); // SIMPLE|COMPOSITE
            if (!Schema::hasColumn('tpr_rules','expression')) $t->text('expression')->nullable()->after('logic_type'); // e.g., "(kri('A') OR kri('B')) AND sla('S1')"
        });
    }
    public function down(): void {
        Schema::table('tpr_rules', function (Blueprint $t) {
            if (Schema::hasColumn('tpr_rules','expression')) $t->dropColumn('expression');
            if (Schema::hasColumn('tpr_rules','logic_type')) $t->dropColumn('logic_type');
            if (Schema::hasColumn('tpr_rules','auto_close_days')) $t->dropColumn('auto_close_days');
            if (Schema::hasColumn('tpr_rules','cool_off_days')) $t->dropColumn('cool_off_days');
        });
    }
};
