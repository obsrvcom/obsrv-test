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
        Schema::create('agent_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('healthy'); // healthy, warning, critical
            $table->json('metrics')->nullable(); // CPU, memory, disk, temperature, etc.
            $table->json('knx_status')->nullable(); // Status of KNX monitors
            $table->integer('uptime')->nullable(); // Seconds
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['agent_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_heartbeats');
    }
};
