<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::table('risks', function(Blueprint $t){
      if(!Schema::hasColumn('risks','category_id')){ $t->foreignId('category_id')->nullable()->constrained('risk_categories')->nullOnDelete(); }
      if(!Schema::hasColumn('risks','org_unit_id')){ $t->foreignId('org_unit_id')->nullable()->constrained('org_units')->nullOnDelete(); }
      if(!Schema::hasColumn('risks','custom_json')){ $t->json('custom_json')->nullable(); }
    });
  }
  public function down(): void {
    Schema::table('risks', function(Blueprint $t){
      if(Schema::hasColumn('risks','category_id')){ $t->dropConstrainedForeignId('category_id'); }
      if(Schema::hasColumn('risks','org_unit_id')){ $t->dropConstrainedForeignId('org_unit_id'); }
      if(Schema::hasColumn('risks','custom_json')){ $t->dropColumn('custom_json'); }
    });
  }
};
