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
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('activity_type'); // created, status_changed, assigned, message_added, etc.
            $table->text('description');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->json('metadata')->nullable(); // for additional context
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
