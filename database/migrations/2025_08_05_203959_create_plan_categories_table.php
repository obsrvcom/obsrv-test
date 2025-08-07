<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create plan categories table
        Schema::create('plan_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Service", "Product", "Maintenance"
            $table->text('description')->nullable();
            $table->string('slug')->nullable(); // URL-friendly version
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('color')->default('#3B82F6'); // For UI styling
            $table->string('icon')->nullable(); // Icon class or name
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'sort_order']);
            $table->unique(['company_id', 'slug']);
        });

        // 2. Add category_id to service_plans_new table
        Schema::table('service_plans_new', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('company_id')->constrained('plan_categories')->onDelete('cascade');
            $table->index(['company_id', 'category_id']);
        });

        // 3. Update feature groups to be per category instead of per company
        Schema::table('service_plan_feature_groups_new', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('company_id')->constrained('plan_categories')->onDelete('cascade');
            $table->index(['company_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_plan_feature_groups_new', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['company_id', 'category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('service_plans_new', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['company_id', 'category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('plan_categories');
    }
};