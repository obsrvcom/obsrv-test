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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['open', 'awaiting_customer', 'on_hold', 'closed'])->default('open');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->timestamp('last_customer_message_at')->nullable();
            $table->timestamp('last_company_message_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['assigned_team_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
