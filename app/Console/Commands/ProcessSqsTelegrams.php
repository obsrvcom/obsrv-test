<?php

namespace App\Console\Commands;

use App\Models\Telegram;
use Aws\Sqs\SqsClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSqsTelegrams extends Command
{
    protected $signature = 'sqs:process-telegrams 
                            {--max-messages=10 : Maximum number of messages to retrieve per request}
                            {--visibility-timeout=300 : Message visibility timeout in seconds}
                            {--wait-time=20 : Long polling wait time in seconds}';
    
    protected $description = 'Process KNX telegram batches from AWS SQS queue';
    
    private SqsClient $sqsClient;
    private string $queueUrl;
    private int $processedCount = 0;
    private int $errorCount = 0;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->initializeSqsClient();
        
        $this->info('Starting SQS telegram processor...');
        $this->info('Queue URL: ' . $this->queueUrl);
        
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        
        while (true) {
            try {
                $this->processMessages();
            } catch (\Exception $e) {
                $this->error('Error processing messages: ' . $e->getMessage());
                Log::error('SQS Processing Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                sleep(5);
            }
        }
    }
    
    private function initializeSqsClient(): void
    {
        $this->sqsClient = new SqsClient([
            'version' => 'latest',
            'region' => config('services.aws.region', 'us-east-1'),
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
        
        $this->queueUrl = config('services.aws.sqs_queue_url');
        
        if (!$this->queueUrl) {
            throw new \Exception('SQS Queue URL not configured');
        }
    }
    
    private function processMessages(): void
    {
        $result = $this->sqsClient->receiveMessage([
            'QueueUrl' => $this->queueUrl,
            'MaxNumberOfMessages' => (int) $this->option('max-messages'),
            'VisibilityTimeout' => (int) $this->option('visibility-timeout'),
            'WaitTimeSeconds' => (int) $this->option('wait-time'),
            'MessageAttributeNames' => ['All'],
            'AttributeNames' => ['All'], // Get message attributes including ApproximateReceiveCount
        ]);
        
        $messages = $result->get('Messages') ?? [];
        
        if (empty($messages)) {
            $this->info('No messages available. Waiting...');
            return;
        }
        
        $this->info('Received ' . count($messages) . ' messages');
        
        foreach ($messages as $message) {
            $this->processMessage($message);
        }
    }
    
    private function processMessage(array $message): void
    {
        $messageId = $message['MessageId'];
        $receiptHandle = $message['ReceiptHandle'];
        
        try {
            // Debug: Log raw message body
            $this->info("Raw message body: " . substr($message['Body'], 0, 500));
            
            $payload = json_decode($message['Body'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON payload: ' . json_last_error_msg());
            }
            
            // Check if payload is nested (AWS IoT might wrap it)
            if (isset($payload['Message'])) {
                $payload = json_decode($payload['Message'], true);
            }
            
            // Handle both message formats
            if (isset($payload['t']) && $payload['t'] === 'kb') {
                // Original format with 't' => 'kb'
                $this->processTelegramBatch($payload, $messageId);
            } elseif (isset($payload['b'])) {
                // New format with 'b' for batch
                $this->processSimpleBatch($payload, $messageId);
            } else {
                throw new \Exception('Unknown message format');
            }
            
            $this->sqsClient->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $receiptHandle,
            ]);
            
            $this->info("Successfully processed message: {$messageId}");
            
        } catch (\Exception $e) {
            $this->errorCount++;
            $this->error("Error processing message {$messageId}: " . $e->getMessage());
            
            Log::error('Failed to process SQS message', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $message['Body'] ?? null,
                'receipt_handle' => $receiptHandle,
            ]);
            
            // Get message attributes to check retry count
            $attributes = $message['Attributes'] ?? [];
            $receiveCount = (int) ($attributes['ApproximateReceiveCount'] ?? 1);
            
            // If message has been received too many times, log critical error
            // SQS will move it to DLQ if configured (recommended: set up DLQ with maxReceiveCount=5)
            if ($receiveCount >= 5) {
                Log::critical('Message exceeded retry limit', [
                    'message_id' => $messageId,
                    'receive_count' => $receiveCount,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    
    private function processTelegramBatch(array $payload, string $messageId): void
    {
        $deviceId = $payload['d'];
        $batchTimestamp = Carbon::createFromTimestamp($payload['ts']);
        $telegrams = $payload['tg'];
        
        $this->info("Processing batch of {$payload['n']} telegrams from device: {$deviceId}");
        
        DB::connection('singlestore')->transaction(function () use ($telegrams, $deviceId, $batchTimestamp, $messageId) {
            $telegramData = [];
            
            foreach ($telegrams as $index => $telegram) {
                $telegramData[] = [
                    'device_id' => $deviceId,
                    'telegram_timestamp' => Carbon::createFromTimestamp($telegram['ts']),
                    'batch_timestamp' => $batchTimestamp,
                    'source' => $telegram['src'],
                    'destination' => $telegram['dst'],
                    'data' => $telegram['d'],
                    'message_code' => $telegram['mc'],
                    'data_value' => $telegram['dv'] ?? null,
                    'direction' => $telegram['dir'] ?? null,
                    'sqs_message_id' => "{$messageId}_{$index}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            foreach (array_chunk($telegramData, 1000) as $chunk) {
                // Use insertOrIgnore to handle duplicates gracefully
                // This prevents duplicate key errors if message is processed twice
                Telegram::insertOrIgnore($chunk);
            }
            
            $this->processedCount += count($telegrams);
        });
        
        $this->info("Inserted {$payload['n']} telegrams. Total processed: {$this->processedCount}");
    }
    
    private function processSimpleBatch(array $payload, string $messageId): void
    {
        $telegrams = $payload['b'];
        $deviceId = $payload['d'] ?? 'unknown';
        $batchTimestamp = isset($payload['ts']) ? Carbon::parse($payload['ts']) : now();
        
        $this->info("Processing batch of " . count($telegrams) . " telegrams");
        
        DB::connection('singlestore')->transaction(function () use ($telegrams, $deviceId, $batchTimestamp, $messageId) {
            $telegramData = [];
            
            foreach ($telegrams as $index => $telegram) {
                // Parse timestamp - handle both Unix timestamp and ISO format
                if (isset($telegram['ts'])) {
                    if (is_numeric($telegram['ts'])) {
                        $telegramTimestamp = Carbon::createFromTimestamp($telegram['ts']);
                    } else {
                        $telegramTimestamp = Carbon::parse($telegram['ts']);
                    }
                } else {
                    $telegramTimestamp = now();
                }
                
                $telegramData[] = [
                    'device_id' => $telegram['if'] ?? $deviceId,
                    'telegram_timestamp' => $telegramTimestamp,
                    'batch_timestamp' => $batchTimestamp,
                    'source' => $telegram['src'] ?? '',
                    'destination' => $telegram['dst'] ?? '',
                    'data' => $telegram['d'] ?? '',
                    'message_code' => $telegram['mc'] ?? '',
                    'data_value' => $telegram['dv'] ?? null,
                    'direction' => $telegram['dir'] ?? null,
                    'sqs_message_id' => "{$messageId}_{$index}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            foreach (array_chunk($telegramData, 1000) as $chunk) {
                // Use insertOrIgnore to handle duplicates gracefully
                // This prevents duplicate key errors if message is processed twice
                Telegram::insertOrIgnore($chunk);
            }
            
            $this->processedCount += count($telegrams);
        });
        
        $this->info("Inserted " . count($telegrams) . " telegrams. Total processed: {$this->processedCount}");
    }
    
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        $this->info("\nReceived shutdown signal. Gracefully stopping...");
        $this->info("Total telegrams processed: {$this->processedCount}");
        $this->info("Total errors: {$this->errorCount}");
        return false;
    }
}