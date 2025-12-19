<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intervention;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Tache;
use Carbon\Carbon;

class TestAutoCompletionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Find or Create Users
        $client = Client::first();
        if (!$client) {
            $this->command->error("No Client found. Please run DatabaseSeeder first.");
            return;
        }

        $intervenant = Intervenant::first();
        if (!$intervenant) {
            $this->command->error("No Intervenant found. Please run DatabaseSeeder first.");
            return;
        }

        $tache = Tache::first();
        if (!$tache) {
            $this->command->error("No Tache found. Please run DatabaseSeeder first.");
            return;
        }

        // 2. Create the Intervention "ending soon"
        // Duration = 1 hour.
        // We want it to end in 5 minutes from NOW.
        // So Start Time = NOW + 5min - 1hour = NOW - 55 minutes.
        
        $duration = 1.0;
        $endsInMinutes = 5;
        $startDate = Carbon::now()->subMinutes((60 * $duration) - $endsInMinutes);

        $intervention = Intervention::create([
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'address' => '123 Test Avenue (Automation Check)',
            'ville' => 'TestCity',
            'status' => 'acceptee', // Critical for the automation to pick it up
            'date_intervention' => $startDate,
            'duration_hours' => $duration,
        ]);

        $this->command->info("Intervention created!");
        $this->command->info("ID: " . $intervention->id);
        $this->command->info("Status: " . $intervention->status);
        $this->command->info("Start: " . $startDate->toDateTimeString());
        $this->command->info("Ends at: " . $startDate->copy()->addHours($duration)->toDateTimeString() . " (approx " . $endsInMinutes . " mins from now)");
        $this->command->comment("Run 'php artisan schedule:work' and wait 5 minutes to see it change to 'termine'.");
    }
}
