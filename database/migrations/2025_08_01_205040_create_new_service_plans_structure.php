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
        // 1. Main Service Plans (e.g., "Standard 2025")
        Schema::create('service_plans_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Standard 2025", "Premium Care 2024"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable(); // For UI styling
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'sort_order']);
        });

        // 2. Service Plan Revisions (draft/published versions)
        Schema::create('service_plan_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained('service_plans_new')->onDelete('cascade');
            $table->string('name'); // e.g., "v1.0", "Draft 2025", "Published"
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_current')->default(false); // Only one current per plan
            $table->integer('version_number')->default(1);
            $table->json('metadata')->nullable(); // Store additional revision info
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['service_plan_id', 'status']);
            $table->index(['service_plan_id', 'is_current']);
        });

        // 3. Service Plan Levels (Level 1, 2, 3, 4 within revisions)
        Schema::create('service_plan_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_revision_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Level 1", "Level 2", "Basic", "Premium"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable(); // For UI styling
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('quarterly_price', 10, 2)->nullable();
            $table->decimal('annual_price', 10, 2)->nullable();
            $table->integer('minimum_contract_months')->nullable();
            $table->timestamps();

            $table->index(['service_plan_revision_id', 'is_active']);
            $table->index(['service_plan_revision_id', 'sort_order']);
        });

        // 4. Feature Groups (groups of features within levels)
        Schema::create('service_plan_feature_groups_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Telephone Support & Remote Diagnostics"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable(); // For UI styling
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'sort_order']);
        });

        // 5. Features (individual features within groups)
        Schema::create('service_plan_features_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_group_id')->constrained('service_plan_feature_groups_new')->onDelete('cascade');
            $table->string('name'); // e.g., "Remote Login Response Time"
            $table->text('description')->nullable();
            $table->enum('data_type', ['boolean', 'text', 'number', 'currency', 'time', 'select'])->default('boolean');
            $table->json('options')->nullable(); // For select type, store options as JSON
            $table->boolean('is_active')->default(true);
            $table->boolean('affects_sla')->default(false); // If this feature affects SLA/response times
            $table->integer('sort_order')->default(0);
            $table->string('unit')->nullable(); // e.g., "hours", "days", "£", "%"
            $table->timestamps();

            $table->index(['feature_group_id', 'is_active']);
            $table->index(['feature_group_id', 'sort_order']);
            $table->index('affects_sla');
        });

        // 6. Pivot table: Which feature groups are included in which levels
        Schema::create('service_plan_level_feature_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('feature_group_id')->constrained('service_plan_feature_groups_new')->onDelete('cascade');
            $table->boolean('is_included')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_plan_level_id', 'feature_group_id'], 'unique_level_feature_group');
            $table->index('service_plan_level_id');
            $table->index('feature_group_id');
        });

        // 7. Feature values for specific levels (customizable per level)
        Schema::create('service_plan_level_feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('service_plan_features_new')->onDelete('cascade');
            $table->text('value')->nullable(); // Stores the actual value (JSON for complex values)
            $table->boolean('is_included')->default(false); // For boolean features
            $table->text('display_value')->nullable(); // Human-readable version for display
            $table->timestamps();

            $table->unique(['service_plan_level_id', 'feature_id'], 'unique_level_feature');
            $table->index('service_plan_level_id');
            $table->index('feature_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plan_level_feature_values');
        Schema::dropIfExists('service_plan_level_feature_groups');
        Schema::dropIfExists('service_plan_features_new');
        Schema::dropIfExists('service_plan_feature_groups_new');
        Schema::dropIfExists('service_plan_levels');
        Schema::dropIfExists('service_plan_revisions');
        Schema::dropIfExists('service_plans_new');
    }
};
