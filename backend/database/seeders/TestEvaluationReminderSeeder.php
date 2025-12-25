<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intervention;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Tache;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Model;

class TestEvaluationReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer l'utilisateur client
        $clientUser = Utilisateur::firstOrCreate(
            ['email' => 'elallouche.zakariyae@etu.uae.ac.ma'],
            [
                'nom' => 'EL ALLOUCHE',
                'prenom' => 'Zakariyae',
                'password' => bcrypt('password'), // Mot de passe par défaut à changer
                'telephone' => '0600000000',
                'address' => 'Adresse du client',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Créer ou récupérer le client associé
        $client = Client::firstOrCreate(
            ['id' => $clientUser->id],
            [
                'address' => 'Adresse du client',
                'ville' => 'Ville du client',
                'is_active' => true,
                'admin_id' => 1 // ID d'admin par défaut
            ]
        );

        // Créer ou récupérer l'utilisateur intervenant
        $intervenantUser = Utilisateur::firstOrCreate(
            ['email' => 'zelallouche@gmail.com'],
            [
                'nom' => 'EL ALLOUCHE',
                'prenom' => 'Zakariyae',
                'password' => bcrypt('password'), // Mot de passe par défaut à changer
                'telephone' => '0600000001',
                'address' => 'Adresse de l\'intervenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Créer ou récupérer l'intervenant associé
        $intervenant = Intervenant::firstOrCreate(
            ['id' => $intervenantUser->id],
            [
                'address' => 'Adresse de l\'intervenant',
                'ville' => 'Ville de l\'intervenant',
                'bio' => 'Biographie de l\'intervenant',
                'is_active' => true,
                'admin_id' => 1 // ID d'admin par défaut
            ]
        );

        // Créer une tâche par défaut si elle n'existe pas
        $tache = Tache::firstOrCreate(
            ['nom_tache' => 'Tâche de test'],
            [
                'description' => 'Tâche de test pour les rappels d\'évaluation',
                'status' => 'actif',
                'service_id' => 1, // ID du service par défaut
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        Model::unguard();

        // 1. Intervention completed 1 day ago (should trigger reminder today)
        $intervention1 = Intervention::create([
            'address' => 'Test Reminder 1 Day',
            'ville' => 'Tetouan',
            'status' => 'termine',
            'date_intervention' => now()->subDays(2),
            'duration_hours' => 2,
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'description' => 'Completed 1 day ago - should get reminder',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ]);

        $this->command->info("Created Intervention #{$intervention1->id} : Completed 1 day ago (should get reminder)");

        // 2. Intervention completed 3 days ago with one reminder already sent yesterday
        $intervention2 = Intervention::create([
            'address' => 'Test Reminder 3 Days',
            'ville' => 'Tetouan',
            'status' => 'termine',
            'date_intervention' => now()->subDays(4),
            'duration_hours' => 2,
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'description' => 'Completed 3 days ago - last reminder 25h ago',
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(3),
            'client_last_reminder_sent_at' => now()->subHours(25),
            'intervenant_last_reminder_sent_at' => now()->subHours(25),
        ]);

        $this->command->info("Created Intervention #{$intervention2->id} : Completed 3 days ago (should get another reminder)");

        // 3. Intervention completed 8 days ago (should NOT get reminder - past 7 days)
        $intervention3 = Intervention::create([
            'address' => 'Test No Reminder 8 Days',
            'ville' => 'Tetouan',
            'status' => 'termine',
            'date_intervention' => now()->subDays(9),
            'duration_hours' => 2,
            'client_id' => $client->id,
            'intervenant_id' => $intervenant->id,
            'tache_id' => $tache->id,
            'description' => 'Completed 8 days ago - should NOT get reminder',
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(8),
        ]);

        $this->command->info("Created Intervention #{$intervention3->id} : Completed 8 days ago (should NOT get reminder)");

        Model::reguard();

        $this->command->info("\nTest data created successfully!");
        $this->command->info("Client email: " . $clientUser->email);
        $this->command->info("Intervenant email: " . $intervenantUser->email);
        
        $this->command->info("\nTriggering evaluation reminders...");
        $this->command->call('evaluations:send-reminders');
        
        $this->command->info("\nExpected: Reminders for interventions #{$intervention1->id} and #{$intervention2->id}");
    }
}
