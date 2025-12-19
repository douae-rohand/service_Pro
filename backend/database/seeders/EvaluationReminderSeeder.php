<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluationReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Setup Test Client
        $clientEmail = 'elallouche.zakariyae@etu.uae.ac.ma';
        
        // CLEANUP: Delete existing test user to allow re-running tests cleanly
        $existingUser = \App\Models\Utilisateur::where('email', $clientEmail)->first();
        if ($existingUser) {
            // Delete related interventions first to avoid constraint errors
            if ($existingUser->client) {
                \App\Models\Intervention::where('client_id', $existingUser->client->id)->delete();
                $existingUser->client->delete();
            }
            $existingUser->delete();
        }

        $clientUser = \App\Models\Utilisateur::create([
            'email' => $clientEmail,
            'nom' => 'Test',
            'prenom' => 'Client',
            'password' => bcrypt('password'),
            'telephone' => '0600000000',
            'address' => '123 Test Street',
            'email_verified_at' => now(),
        ]);

        $client = \App\Models\Client::create(['id' => $clientUser->id]);

        // 2. Setup Test Intervenant
        $intervenantEmail = 'allouchezaki45@gmail.com';
        // Cleanup intervenant too if needed but simpler to get or create
        $intervenantUser = \App\Models\Utilisateur::firstOrCreate(
            ['email' => $intervenantEmail],
            [
                'nom' => 'Test',
                'prenom' => 'Intervenant',
                'password' => bcrypt('password'),
                'telephone' => '0611111111',
                'address' => '456 Worker Ave',
                'email_verified_at' => now(),
            ]
        );

        $intervenant = \App\Models\Intervenant::firstOrCreate(
            ['id' => $intervenantUser->id],
            [
                'ville' => 'Casablanca',
                'bio' => 'Professionnel de test pour les rappels.',
                'is_active' => true
            ]
        );

        // 3. Ensure Service/Tache
        $service = \App\Models\Service::firstOrCreate(
            ['nom_service' => 'Ménage'],
            ['description' => 'Service de ménage test', 'image_url' => 'test.jpg']
        );
        
        $tache = \App\Models\Tache::firstOrCreate(
            ['nom_tache' => 'Nettoyage Standard'],
            ['description' => 'Nettoyage de base pour test', 'service_id' => $service->id]
        );

        // 4. Test Cases

        // Case C: IMMEDIATE - Finished 1 minute ago
        \App\Models\Intervention::create([
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'status' => 'termine',
            'date_intervention' => now()->subMinutes(1), 
            'duration_hours' => 1,
            'address' => '789 Bd Recent (Cas C)',
            'ville' => 'Casablanca',
            'description' => 'TEST: Terminé il y a 1 min (Doit envoyer email)',
            'created_at' => now()->subMinutes(1),
            'reminder_count' => 0,
            'last_reminder_sent_at' => null
        ]);

        // Case F: "Future" Simulation - Finished 3 minutes ago
        \App\Models\Intervention::create([
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'status' => 'termine',
            'date_intervention' => now()->subMinutes(3), 
            'duration_hours' => 1,
            'address' => '101 Bd Futur (Cas F)',
            'ville' => 'Casablanca',
            'description' => 'TEST: Terminé il y a 3 min (Doit envoyer email)',
            'created_at' => now()->subMinutes(3),
            'reminder_count' => 0,
            'last_reminder_sent_at' => null
        ]);

        // Case E: Reminder sent 5 hours ago (Debounce, NO reminder)
        \App\Models\Intervention::create([
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'status' => 'termine',
            'date_intervention' => now()->subDays(4),
            'duration_hours' => 2,
            'address' => '654 Rue Patience (Cas E)',
            'ville' => 'Casablanca',
            'description' => 'Rappel envoyé aujourd\'hui (Pas de nouveau rappel)',
            'created_at' => now()->subDays(4),
            'reminder_count' => 1,
            'last_reminder_sent_at' => now()->subHours(5)
        ]);
    }
}
