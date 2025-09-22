<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tpr_rule_suppressions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rule_id')->nullable(); // null=all rules
            $t->foreignId('vendor_id')->nullable(); // null=all vendors
            $t->date('until');
            $t->string('reason')->nullable();
            $t->timestamps();
            $t->index(['rule_id','vendor_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('tpr_rule_suppressions');
    }
};
