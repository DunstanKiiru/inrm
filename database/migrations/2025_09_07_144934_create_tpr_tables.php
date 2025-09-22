<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tpr_vendors', function (Blueprint $t) {
            $t->id(); $t->string('code')->unique(); $t->string('name');
            $t->string('tier')->nullable(); // 1|2|3
            $t->string('criticality')->nullable(); // high|medium|low
            $t->string('status')->default('active'); // active|inactive|onboarding
            $t->string('category')->nullable(); // SaaS, Payments, Cloud, etc.
            $t->json('tags')->nullable();
            $t->float('inherent_score')->nullable();
            $t->float('residual_score')->nullable();
            $t->timestamps();
        });
        Schema::create('tpr_vendor_contacts', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->string('name')->nullable(); $t->string('email')->nullable(); $t->string('role')->nullable();
            $t->timestamps();
        });
        Schema::create('tpr_vendor_risk', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->foreignId('risk_id')->constrained('risks'); $t->timestamps();
            $t->unique(['vendor_id','risk_id']);
        });

        // Assessments
        Schema::create('tpr_assessment_templates', function (Blueprint $t) {
            $t->id(); $t->string('code')->unique(); $t->string('title');
            $t->text('description')->nullable(); $t->json('meta')->nullable();
            $t->timestamps();
        });
        Schema::create('tpr_assessment_questions', function (Blueprint $t) {
            $t->id(); $t->foreignId('template_id')->constrained('tpr_assessment_templates');
            $t->string('code')->nullable(); $t->text('question'); $t->string('type')->default('text'); // text|single|multi|score
            $t->json('options')->nullable(); // for single/multi
            $t->integer('weight')->default(1);
            $t->timestamps();
        });
        Schema::create('tpr_assessments', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->foreignId('template_id')->constrained('tpr_assessment_templates');
            $t->date('sent_at')->nullable(); $t->date('due_at')->nullable(); $t->date('completed_at')->nullable();
            $t->string('status')->default('draft'); // draft|sent|in_progress|completed|scored
            $t->float('score')->nullable();
            $t->float('residual_score')->nullable();
            $t->timestamps();
        });
        Schema::create('tpr_assessment_responses', function (Blueprint $t) {
            $t->id(); $t->foreignId('assessment_id')->constrained('tpr_assessments');
            $t->foreignId('question_id')->constrained('tpr_assessment_questions');
            $t->text('answer_text')->nullable();
            $t->json('answer_options')->nullable();
            $t->float('score')->nullable();
            $t->string('evidence_path')->nullable();
            $t->timestamps();
            $t->unique(['assessment_id','question_id']);
        });

        // KRIs
        Schema::create('tpr_vendor_kri_defs', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->string('code'); $t->string('name'); $t->string('unit')->nullable();
            $t->float('green_max')->nullable(); $t->float('amber_max')->nullable(); // >amber_max==red
            $t->timestamps();
            $t->unique(['vendor_id','code']);
        });
        Schema::create('tpr_vendor_kri_measures', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->foreignId('kri_id')->nullable(); // link to def if known
            $t->string('kri_code'); $t->timestamp('measured_at');
            $t->float('value')->nullable(); $t->string('status')->nullable(); // ok|alert|breach
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        // SLAs
        Schema::create('tpr_vendor_sla_defs', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->string('code'); $t->string('name'); $t->string('unit')->nullable();
            $t->float('target')->nullable();
            $t->timestamps();
            $t->unique(['vendor_id','code']);
        });
        Schema::create('tpr_vendor_sla_measures', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->foreignId('sla_id')->nullable();
            $t->string('sla_code'); $t->timestamp('measured_at');
            $t->float('value')->nullable(); $t->string('status')->nullable(); // ok|breach
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        // Documents (contracts, SOC reports, etc.)
        Schema::create('tpr_vendor_documents', function (Blueprint $t) {
            $t->id(); $t->foreignId('vendor_id')->constrained('tpr_vendors');
            $t->string('type'); // contract|soc2|iso|dpa|custom
            $t->string('path'); $t->string('filename')->nullable(); $t->string('mime')->nullable();
            $t->date('effective_date')->nullable(); $t->date('expiry_date')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tpr_vendor_documents');
        Schema::dropIfExists('tpr_vendor_sla_measures');
        Schema::dropIfExists('tpr_vendor_sla_defs');
        Schema::dropIfExists('tpr_vendor_kri_measures');
        Schema::dropIfExists('tpr_vendor_kri_defs');
        Schema::dropIfExists('tpr_assessment_responses');
        Schema::dropIfExists('tpr_assessments');
        Schema::dropIfExists('tpr_assessment_questions');
        Schema::dropIfExists('tpr_assessment_templates');
        Schema::dropIfExists('tpr_vendor_risk');
        Schema::dropIfExists('tpr_vendor_contacts');
        Schema::dropIfExists('tpr_vendors');
    }
};
