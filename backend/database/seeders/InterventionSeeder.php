<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InterventionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Statuts: 'en attend', 'acceptee', 'refuse', 'termine'
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('intervention')->insert([
            // ============================================
            // INTERVENTIONS TERMINÉES (pour tests évaluations)
            // ============================================
            
            [
                'client_id' => 2, // Mohammed Alami
                'intervenant_id' => 7, // Amina Tazi
                'tache_id' => 1, // Tonte de pelouse
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => $now->copy()->subDays(30)->format('Y-m-d'),
                'duration_hours' => 2.5,
                'description' => 'Tonte complète du jardin avec ramassage des déchets. Travail soigné et professionnel.',
                'status' => 'termine',
                'created_at' => $now->copy()->subDays(35),
                'updated_at' => $now->copy()->subDays(30),
            ],
            [
                'client_id' => 3, // Fatima Benjelloun
                'intervenant_id' => 8, // Youssef Benslimane
                'tache_id' => 2, // Taille de haies
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'date_intervention' => $now->copy()->subDays(25)->format('Y-m-d'),
                'duration_hours' => 3.0,
                'description' => 'Taille des haies et élagage des arbres. Résultat impeccable.',
                'status' => 'termine',
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now->copy()->subDays(25),
            ],
            [
                'client_id' => 4, // Karim El Fassi
                'intervenant_id' => 9, // Sara Chraibi
                'tache_id' => 7, // Ménage résidentiel & régulier
                'address' => '12 Rue de la Liberté, Marrakech',
                'ville' => 'Marrakech',
                'date_intervention' => $now->copy()->subDays(20)->format('Y-m-d'),
                'duration_hours' => 4.0,
                'description' => 'Nettoyage complet de la maison : toutes les pièces, vitres, sols, cuisine et salle de bain.',
                'status' => 'termine',
                'created_at' => $now->copy()->subDays(25),
                'updated_at' => $now->copy()->subDays(20),
            ],
            [
                'client_id' => 5, // Leila Idrissi
                'intervenant_id' => 10, // Hassan Radi
                'tache_id' => 8, // Nettoyage en profondeur
                'address' => '90 Rue Oued Fès, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => $now->copy()->subDays(15)->format('Y-m-d'),
                'duration_hours' => 5.5,
                'description' => 'Nettoyage en profondeur complet : désinfection, nettoyage derrière meubles, détartrage, lavage des murs.',
                'status' => 'termine',
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(15),
            ],
            [
                'client_id' => 2, // Mohammed Alami
                'intervenant_id' => 11, // Ahmed Bennani
                'tache_id' => 3, // Plantation de fleurs
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => $now->copy()->subDays(10)->format('Y-m-d'),
                'duration_hours' => 3.5,
                'description' => 'Création d\'un massif floral avec différentes variétés. Très satisfait du résultat.',
                'status' => 'termine',
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(10),
            ],
            
            // ============================================
            // INTERVENTIONS ACCEPTÉES (prochaines)
            // ============================================
            
            [
                'client_id' => 3, // Fatima Benjelloun
                'intervenant_id' => 9, // Sara Chraibi
                'tache_id' => 7, // Ménage résidentiel & régulier
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'date_intervention' => $now->copy()->addDays(5)->format('Y-m-d'),
                'duration_hours' => 4.0,
                'description' => 'Nettoyage régulier de la maison : toutes les pièces principales.',
                'status' => 'acceptee',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'client_id' => 4, // Karim El Fassi
                'intervenant_id' => 7, // Amina Tazi
                'tache_id' => 4, // Élagage d'arbres
                'address' => '12 Rue de la Liberté, Marrakech',
                'ville' => 'Marrakech',
                'date_intervention' => $now->copy()->addDays(8)->format('Y-m-d'),
                'duration_hours' => 4.0,
                'description' => 'Élagage de 3 arbres fruitiers dans le jardin.',
                'status' => 'acceptee',
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(4),
            ],
            [
                'client_id' => 6, // Omar Mansouri
                'intervenant_id' => 12, // Khadija Alaoui
                'tache_id' => 7, // Ménage résidentiel & régulier
                'address' => '15 Avenue Allal Ben Abdellah, Fes',
                'ville' => 'Fes',
                'date_intervention' => $now->copy()->addDays(12)->format('Y-m-d'),
                'duration_hours' => 3.5,
                'description' => 'Nettoyage complet de l\'appartement.',
                'status' => 'acceptee',
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(6),
            ],
            
            // ============================================
            // INTERVENTIONS EN ATTENTE
            // ============================================
            
            [
                'client_id' => 2, // Mohammed Alami
                'intervenant_id' => 15, // Samir Lahlou
                'tache_id' => 5, // Désherbage
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => $now->copy()->addDays(7)->format('Y-m-d'),
                'duration_hours' => 2.0,
                'description' => 'Désherbage complet du jardin et des allées.',
                'status' => 'en attend',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'client_id' => 5, // Leila Idrissi
                'intervenant_id' => 13, // Mehdi Berrada
                'tache_id' => 7, // Ménage résidentiel & régulier
                'address' => '90 Rue Oued Fès, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => $now->copy()->addDays(10)->format('Y-m-d'),
                'duration_hours' => 4.0,
                'description' => 'Nettoyage complet avant réception.',
                'status' => 'en attend',
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'client_id' => 3, // Fatima Benjelloun
                'intervenant_id' => 11, // Ahmed Bennani
                'tache_id' => 6, // Entretien de potager
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'date_intervention' => $now->copy()->addDays(14)->format('Y-m-d'),
                'duration_hours' => 3.0,
                'description' => 'Entretien du potager : arrosage, désherbage et récolte.',
                'status' => 'en attend',
                'created_at' => $now->copy()->subHours(12),
                'updated_at' => $now->copy()->subHours(12),
            ],
            
            // ============================================
            // INTERVENTION REFUSÉE (pour test)
            // ============================================
            
            [
                'client_id' => 4, // Karim El Fassi
                'intervenant_id' => 8, // Youssef Benslimane
                'tache_id' => 1, // Tonte de pelouse
                'address' => '12 Rue de la Liberté, Marrakech',
                'ville' => 'Marrakech',
                'date_intervention' => $now->copy()->addDays(6)->format('Y-m-d'),
                'duration_hours' => 2.5,
                'description' => 'Tonte de pelouse - date non disponible pour l\'intervenant.',
                'status' => 'refuse',
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now->copy()->subDays(3),
            ],
            
            // ============================================
            // INTERVENTIONS FUTURES (pour dashboard)
            // ============================================
            
            [
                'client_id' => 6, // Omar Mansouri
                'intervenant_id' => 15, // Samir Lahlou
                'tache_id' => 3, // Plantation de fleurs
                'address' => '15 Avenue Allal Ben Abdellah, Fes',
                'ville' => 'Fes',
                'date_intervention' => $now->copy()->addDays(20)->format('Y-m-d'),
                'duration_hours' => 4.0,
                'description' => 'Création d\'un nouveau massif floral dans le jardin.',
                'status' => 'acceptee',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(9),
            ],
        ]);
    }
}
