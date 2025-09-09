<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'singlestore';
    
    public function up(): void
    {
        Schema::connection('singlestore')->create('telegrams', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50);
            $table->timestamp('telegram_timestamp', 6);
            $table->timestamp('batch_timestamp', 6)->nullable();
            $table->string('source', 20);
            $table->string('destination', 20);
            $table->string('data');
            $table->string('message_code', 50);
            $table->text('data_value')->nullable();
            $table->string('direction', 10)->nullable();
            $table->string('sqs_message_id', 100)->nullable();
            $table->timestamps();
            
            $table->index('device_id');
            $table->index('telegram_timestamp');
            $table->index('destination');
            $table->index(['device_id', 'telegram_timestamp']);
            $table->index(['destination', 'telegram_timestamp']);
            // SingleStore doesn't support unique on non-shard key, use regular index
            $table->index('sqs_message_id');
        });
    }

    public function down(): void
    {
        Schema::connection('singlestore')->dropIfExists('telegrams');
    }
};