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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('hold_until')->nullable()->after('last_company_message_at');
            $table->text('hold_reason')->nullable()->after('hold_until');

            $table->index(['status', 'hold_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'hold_until']);
            $table->dropColumn(['hold_until', 'hold_reason']);
        });
    }
};
