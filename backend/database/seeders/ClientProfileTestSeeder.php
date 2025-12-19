<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientProfileTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = 2; // Target Client
        $me = 5;       // Current Intervenant (Intervenant 5)
        $others = [6, 7, 8]; // Other Intervenants
        
        // Client Criteria IDs (from CritaireSeeder or database)
        // Usually 1-5 for 'client' type
        $criteriaIds = [1, 2, 3, 4, 5];

        // --- SCENARIO 1: Shared History (ME + CLIENT) ---
        
        // 1. Fully Rated shared intervention (1 month ago)
        $shared1 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1, // Tonte de pelouse
            'address' => '45 Avenue Hassan II, Rabat',
            'ville' => 'Rabat',
            'date_intervention' => Carbon::now()->subMonths(1)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subMonths(1)->subDays(2),
            'updated_at' => Carbon::now()->subMonths(1),
        ]);

        // Ratings for shared1 (Public because both rated)
        foreach ($criteriaIds as $cid) {
            DB::table('evaluation')->insert([
                ['note' => 5, 'intervention_id' => $shared1, 'critaire_id' => $cid, 'type_auteur' => 'intervenant', 'created_at' => now()],
                ['note' => 4, 'intervention_id' => $shared1, 'critaire_id' => $cid + 5, 'type_auteur' => 'client', 'created_at' => now()],
            ]);
        }
        DB::table('commentaire')->insert([
            ['commentaire' => 'Client très ponctuel et sympathique. Jardin bien préparé.', 'intervention_id' => $shared1, 'type_auteur' => 'intervenant', 'created_at' => now()],
            ['commentaire' => 'Travail excellent, merci beaucoup !', 'intervention_id' => $shared1, 'type_auteur' => 'client', 'created_at' => now()],
        ]);

        // 2. Recent intervention (3 days ago) - ONLY CLIENT RATED (Private)
        $shared2 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 2, // Taille de haies
            'address' => '45 Avenue Hassan II, Rabat',
            'ville' => 'Rabat',
            'date_intervention' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        // Only client rated. Since it's < 7 days and intervenant hasn't rated, it should be PRIVATE
        DB::table('evaluation')->insert([
            ['note' => 3, 'intervention_id' => $shared2, 'critaire_id' => 6, 'type_auteur' => 'client', 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Un peu de retard cette fois-ci.', 'intervention_id' => $shared2, 'type_auteur' => 'client', 'created_at' => now()],
        ]);


        // --- SCENARIO 2: External History (OTHERS + CLIENT) ---
        
        // 3. Public evaluation from Intervenant 6 (15 days ago)
        $ext1 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $others[0],
            'tache_id' => 1,
            'address' => '12 Rue de la Paix, Casablanca',
            'ville' => 'Casablanca',
            'date_intervention' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(20),
            'updated_at' => Carbon::now()->subDays(15),
        ]);

        // Both rated -> Public
        DB::table('evaluation')->insert([
            ['note' => 4, 'intervention_id' => $ext1, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'created_at' => now()],
            ['note' => 5, 'intervention_id' => $ext1, 'critaire_id' => 2, 'type_auteur' => 'intervenant', 'created_at' => now()],
            ['note' => 5, 'intervention_id' => $ext1, 'critaire_id' => 6, 'type_auteur' => 'client', 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Instructions très claires. Le client sait ce qu\'il veut.', 'intervention_id' => $ext1, 'type_auteur' => 'intervenant', 'created_at' => now()],
        ]);

        // 4. Public evaluation from Intervenant 7 (10 days ago)
        $ext2 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $others[1],
            'tache_id' => 6, // Ménage
            'address' => 'Rabat Residence Al Massira',
            'ville' => 'Rabat',
            'date_intervention' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(12),
            'updated_at' => Carbon::now()->subDays(10),
        ]);

        // Window passed (10 days > 7 days) -> Should be Public even if one didn't rate
        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $ext2, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Excellent client. Très ponctuel.', 'intervention_id' => $ext2, 'type_auteur' => 'intervenant', 'created_at' => now()],
        ]);

        // --- ENHANCEMENT: Add Materials and Infos for testing Details Modal ---
        
        // Add materials for shared1
        DB::table('intervention_materiel')->insert([
            ['intervention_id' => $shared1, 'materiel_id' => 1, 'created_at' => now(), 'updated_at' => now()], // Tondeuse
            ['intervention_id' => $shared1, 'materiel_id' => 4, 'created_at' => now(), 'updated_at' => now()], // Râteau
        ]);

        // Add info for shared1
        DB::table('intervention_information')->insert([
            ['intervention_id' => $shared1, 'information_id' => 1, 'information' => '250 m²', 'created_at' => now(), 'updated_at' => now()], // Surface
            ['intervention_id' => $shared1, 'information_id' => 5, 'information' => 'Normal', 'created_at' => now(), 'updated_at' => now()], // Urgence
        ]);

        // Add materials for shared2
        DB::table('intervention_materiel')->insert([
            ['intervention_id' => $shared2, 'materiel_id' => 2, 'created_at' => now(), 'updated_at' => now()], // Taille-haie
        ]);

        // Add info for shared2
        DB::table('intervention_information')->insert([
            ['intervention_id' => $shared2, 'information_id' => 1, 'information' => '50 mètres linéaires', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Public evaluation from Intervenant 8 (20 days ago)
        $ext3 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $others[2],
            'tache_id' => 7, // Ménage en profondeur
            'address' => 'Hay Riad, Rabat',
            'ville' => 'Rabat',
            'date_intervention' => Carbon::now()->subDays(20)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(25),
            'updated_at' => Carbon::now()->subDays(20),
        ]);

        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $ext3, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'created_at' => now()],
            ['note' => 5, 'intervention_id' => $ext3, 'critaire_id' => 2, 'type_auteur' => 'intervenant', 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Fantastique ! Maison très spacieuse et client accueillant.', 'intervention_id' => $ext3, 'type_auteur' => 'intervenant', 'created_at' => now()],
        ]);

        $this->command->info('Client Profile Test Seeder executed successfully for Client 2 and Intervenant 5!');
    }
}
