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
        Schema::create('pairing_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('device_id');
            $table->string('thing_name')->nullable();
            $table->boolean('used')->default(false);
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('paired_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('device_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pairing_codes');
    }
};
