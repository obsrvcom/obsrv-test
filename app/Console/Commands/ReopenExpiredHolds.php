<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChatSession;

class ReopenExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chats:reopen-expired-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reopen chat sessions that have expired holds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSessions = ChatSession::withExpiredHolds()->get();

        if ($expiredSessions->isEmpty()) {
            $this->info('No expired holds found.');
            return 0;
        }

        $reopenedCount = 0;

        foreach ($expiredSessions as $session) {
            if ($session->reopenIfExpired()) {
                $reopenedCount++;
                $this->info("Reopened chat session #{$session->id} for site: {$session->site->name}");
            }
        }

        $this->info("Successfully reopened {$reopenedCount} chat session(s) with expired holds.");

        return 0;
    }
}
