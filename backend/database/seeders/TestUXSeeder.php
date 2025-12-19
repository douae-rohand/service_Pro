<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intervention;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Tache;
use Carbon\Carbon;

class TestUXSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Get Base Data
        $client = Client::first() ?? Client::factory()->create();
        $intervenant = Intervenant::first() ?? Intervenant::factory()->create();
        $tache = Tache::first() ?? Tache::factory()->create();

        // 2. Create Pending Interventions (For Accept/Refuse Test)
        $this->command->info("Creating 3 Pending Interventions for Accept/Refuse testing...");
        
        for ($i = 1; $i <= 3; $i++) {
            Intervention::create([
                'client_id' => $client->id,
                'intervenant_id' => $intervenant->id,
                'tache_id' => $tache->id,
                'address' => "$i Rue de Test Pending",
                'ville' => 'TestCity',
                'status' => 'en attend', // Pending status
                'date_intervention' => Carbon::tomorrow()->addHours($i),
                'duration_hours' => 2.0,
            ]);
        }

        // 3. Create "Auto-Complete" Intervention (For Auto-Switch Test)
        // Ends in 1 minute from NOW.
        $duration = 1.0;
        $endsInMinutes = 1;
        $startDate = Carbon::now()->subMinutes((60 * $duration) - $endsInMinutes);

        $autoIntervention = Intervention::create([
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'address' => 'Auto-Switch Test Avenue',
            'ville' => 'TestCity',
            'status' => 'acceptee', // Accepted status, ready to complete
            'date_intervention' => $startDate,
            'duration_hours' => $duration,
        ]);

        $this->command->info("Created 3 'En Attente' interventions.");
        $this->command->info("Created 1 'Acceptee' intervention (ID: {$autoIntervention->id}) that ends in {$endsInMinutes} minute.");
        $this->command->comment("Run 'php artisan schedule:work' immediately to catch the auto-switch!");
    }
}
