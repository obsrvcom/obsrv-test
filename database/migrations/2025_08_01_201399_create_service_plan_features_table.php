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
        if (!Schema::hasTable('service_plan_features')) {
            Schema::create('service_plan_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_plan_feature_category_id')->constrained()->onDelete('cascade');
                $table->string('name'); // e.g., "Remote Login Response Time"
                $table->text('description')->nullable();
                $table->enum('data_type', ['boolean', 'text', 'number', 'currency', 'time', 'select'])->default('boolean');
                $table->json('options')->nullable(); // For select type, store options as JSON
                $table->boolean('is_active')->default(true);
                $table->boolean('affects_sla')->default(false); // If this feature affects SLA/response times
                $table->integer('sort_order')->default(0);
                $table->string('unit')->nullable(); // e.g., "hours", "days", "£", "%"
                $table->timestamps();

                $table->index(['service_plan_feature_category_id', 'is_active'], 'spf_category_active_idx');
                $table->index(['service_plan_feature_category_id', 'sort_order'], 'spf_category_sort_idx');
                $table->index('affects_sla');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plan_features');
    }
};
