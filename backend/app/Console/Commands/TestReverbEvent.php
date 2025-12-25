<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServiceRequestStatusUpdated;
use Illuminate\Support\Facades\Log;

class TestReverbEvent extends Command
{
    protected $signature = 'test:reverb {intervenantId=5}';
    protected $description = 'Test Reverb event broadcasting';

    public function handle()
    {
        $intervenantId = $this->argument('intervenantId');
        
        $this->info("🔔 Dispatching test event to intervenant {$intervenantId}");
        
        Log::info("Test event dispatched", [
            'intervenant_id' => $intervenantId,
            'channel' => 'intervenant.' . $intervenantId
        ]);
        
        ServiceRequestStatusUpdated::dispatch(
            $intervenantId, 
            'Test Service', 
            'active', 
            'This is a test notification from Artisan command!'
        );
        
        $this->info("✅ Event dispatched successfully!");
        $this->info("Check your browser console for the notification.");
        
        return 0;
    }
}
