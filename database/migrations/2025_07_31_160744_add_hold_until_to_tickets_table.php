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
        if (!Schema::hasColumn('tickets', 'hold_until')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->timestamp('hold_until')->nullable()->after('last_company_message_at');
            });
        }
        
        if (!Schema::hasColumn('tickets', 'hold_reason')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->text('hold_reason')->nullable()->after('hold_until');
            });
        }

        // Add index - will be skipped if it already exists
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['status', 'hold_until']);
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }
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
