<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'singlestore';
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('singlestore')->create('telegrams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('knx_network_id');
            $table->unsignedBigInteger('knx_group_address_id')->nullable();
            $table->string('data_raw', 255)->nullable();
            $table->string('data_formatted', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->unsignedBigInteger('knx_data_type_id')->nullable();
            $table->integer('local_seq')->nullable();
            $table->integer('local_seq_last')->nullable();
            $table->boolean('sampled')->default(0);
            $table->string('sampled_min', 255)->nullable();
            $table->string('sampled_max', 255)->nullable();
            $table->string('sampled_avg', 255)->nullable();
            $table->integer('sampled_count_telegrams')->nullable();
            $table->integer('sampled_count_changes')->nullable();
            $table->integer('sampled_errors')->nullable();
            $table->timestamp('sampled_start_time')->nullable();
            $table->timestamp('sampled_end_time')->nullable();
            $table->integer('repeats')->default(0);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('sampled_at')->nullable();
            $table->timestamp('repeated_at')->nullable();
            
            // Add indexes for better query performance
            $table->index('knx_network_id');
            $table->index('knx_group_address_id');
            $table->index(['knx_network_id', 'knx_group_address_id', 'recorded_at'], 'idx_network_group_recorded');
            $table->index(['knx_network_id', 'knx_group_address_id', 'repeated_at'], 'idx_network_group_repeated');
            $table->index(['knx_network_id', 'repeated_at'], 'idx_knx_network_repeated');
            $table->index(['knx_network_id', 'recorded_at'], 'idx_knx_network_recorded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('singlestore')->dropIfExists('telegrams');
    }
};