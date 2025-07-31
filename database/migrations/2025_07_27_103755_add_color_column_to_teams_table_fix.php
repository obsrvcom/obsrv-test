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
        // Check if column doesn't exist before adding it
        if (!Schema::hasColumn('teams', 'color')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->string('color')->default('blue')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
