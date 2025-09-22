<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('controls')) {
            Schema::create('controls', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->text('description')->nullable();
                $t->foreignId('category_id')
                    ->nullable()
                    ->constrained('control_categories')
                    ->nullOnDelete();
                $t->string('nature')->nullable();    // preventive|detective|corrective
                $t->string('type')->nullable();      // manual|automated
                $t->string('frequency')->nullable(); // daily|weekly|monthly|annual|ad-hoc
                $t->foreignId('owner_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $t->string('status')->default('active'); // draft|active|retired
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('controls');
    }
};
