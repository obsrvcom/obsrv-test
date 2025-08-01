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
        Schema::create('service_plan_feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_plan_feature_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable(); // Stores the actual value (JSON for complex values)
            $table->boolean('is_included')->default(false); // For boolean features
            $table->text('display_value')->nullable(); // Human-readable version for display
            $table->timestamps();

            // Ensure unique combination of plan and feature
            $table->unique(['service_plan_id', 'service_plan_feature_id'], 'unique_plan_feature');
            $table->index('service_plan_id');
            $table->index('service_plan_feature_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plan_feature_values');
    }
};
