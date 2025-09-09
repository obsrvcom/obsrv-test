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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('thing_name')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('type')->default('raspberry-pi-5');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('offline'); // online, offline, provisioning, pairing
            $table->string('firmware_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('config')->nullable();
            $table->json('knx_monitors')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('paired_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
            
            $table->index('site_id');
            $table->index('status');
            $table->index('last_heartbeat_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
