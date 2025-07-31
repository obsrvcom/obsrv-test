<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class ReopenExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:reopen-expired-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reopen tickets that have expired hold periods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTickets = Ticket::where('status', 'on_hold')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now())
            ->get();

        $reopenedCount = 0;

        foreach ($expiredTickets as $ticket) {
            $ticket->update([
                'status' => 'open',
                'hold_until' => null,
                'hold_reason' => null,
            ]);

            $ticket->logActivity(
                'status_changed',
                'Ticket automatically reopened after hold period expired',
                'on_hold',
                'open'
            );

            $reopenedCount++;

            $this->info("Reopened ticket {$ticket->ticket_number}");
        }

        $this->info("Reopened {$reopenedCount} expired tickets.");

        return Command::SUCCESS;
    }
}
