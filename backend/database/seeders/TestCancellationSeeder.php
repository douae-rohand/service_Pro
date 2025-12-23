<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intervention;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Tache;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Model;

class TestCancellationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Specific email requested by user
        $targetEmail = 'elallouche.zakariyae@etu.uae.ac.ma';
        
        $user = Utilisateur::where('email', $targetEmail)->first();
        if (!$user) {
            $user = Utilisateur::create([
                'nom' => 'Test',
                'prenom' => 'Client',
                'email' => $targetEmail,
                'password' => bcrypt('password'),
                'telephone' => '0600000000',
                'estActive' => true,
            ]);
        }

        // Client table uses 'id' as both PK and FK to utilisateur
        $client = Client::find($user->id);
        if (!$client) {
            $client = Client::create([
                'id' => $user->id,
                'address' => 'Test Address',
                'ville' => 'Tetouan',
            ]);
        }

        $intervenant = Intervenant::first();
        if (!$intervenant) {
            $user = Utilisateur::firstWhere('role', 'intervenant') ?? Utilisateur::factory()->create(['role' => 'intervenant']);
            $intervenant = Intervenant::create(['utilisateur_id' => $user->id, 'bio' => 'Test', 'ville' => 'Test', 'telephone' => '0600000000']);
        }

        $tache = Tache::first();
        if (!$tache) {
            $tache = Tache::create(['nom_tache' => 'Test Task', 'description' => 'Test', 'tarif_estime' => 100, 'service_id' => 1]); // Assuming service 1 exists
        }

        Model::unguard();

        // 1. Intervention that will be stale in 2 minutes (Created 47h 58m ago)
        // This allows seeing the cancellation happen almost immediately by waiting a bit
        $interventionSoon = Intervention::create([
            'address' => 'Test Cancel Soon',
            'ville' => 'Test City',
            'status' => 'en attend',
            'date_intervention' => now()->addDays(2),
            'duration_hours' => 2,
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'description' => 'Will expire in 2 minutes',
            'created_at' => now()->subHours(48)->addMinutes(2),
            'updated_at' => now()->subHours(48)->addMinutes(2),
        ]);

        $this->command->info("Created Intervention #{$interventionSoon->id} : Expires in 2 minutes.");

        // 2. Intervention that will be stale in 15 minutes (Created 47h 45m ago)
        // Requested by user
        $interventionLater = Intervention::create([
            'address' => 'Test Cancel Later',
            'ville' => 'Test City',
            'status' => 'en attend',
            'date_intervention' => now()->addDays(3),
            'duration_hours' => 2,
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'description' => 'Will expire in 15 minutes',
            'created_at' => now()->subHours(48)->addMinutes(15),
            'updated_at' => now()->subHours(48)->addMinutes(15),
        ]);
        
        $this->command->info("Created Intervention #{$interventionLater->id} : Expires in 15 minutes.");

        Model::reguard();
    }
}
