<?php

namespace Webkul\Newsletters\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Newsletters\Events\MailingListStatsUpdated;

class TestWebSocketBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'newsletters:test-broadcast {mailing_list_id}';

    /**
     * The console command description.
     */
    protected $description = 'Test WebSocket broadcasting for mailing list stats';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mailingListId = $this->argument('mailing_list_id');
        
        $this->info("Testing WebSocket broadcast for mailing list ID: {$mailingListId}");
        
        // Broadcast test event
        broadcast(new MailingListStatsUpdated($mailingListId, [
            'sent_count' => rand(1, 100),
            'incoming_count' => rand(1, 50),
            'viewed_count' => rand(1, 80),
            'total_count' => rand(50, 200)
        ]));
        
        $this->info('Broadcast event sent successfully!');
        $this->info('Check your browser console and the mailing lists page for updates.');
        
        return 0;
    }
}
