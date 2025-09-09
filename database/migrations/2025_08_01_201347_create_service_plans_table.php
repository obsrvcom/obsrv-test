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
        if (!Schema::hasTable('service_plans')) {
            Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_group_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Level 1", "Level 2", "No Cover"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable(); // For UI styling
            $table->decimal('base_price_monthly', 10, 2)->nullable();
            $table->decimal('base_price_quarterly', 10, 2)->nullable();
            $table->decimal('base_price_annually', 10, 2)->nullable();
            $table->integer('minimum_contract_months')->nullable();
            $table->timestamps();

            $table->index(['service_plan_group_id', 'is_active']);
            $table->index(['service_plan_group_id', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
