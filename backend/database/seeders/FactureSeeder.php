<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FactureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('facture')->insert([
            // Factures pour interventions terminées
            [
                'intervention_id' => 1, // Client 2, Intervenant 7 - Tonte pelouse (2.5h)
                'fichier_path' => '/uploads/factures/facture_intervention_1.pdf',
                'ttc' => 200.00, // 80€/h * 2.5h
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now->copy()->subDays(30),
            ],
            [
                'intervention_id' => 2, // Client 3, Intervenant 8 - Taille haies (3h)
                'fichier_path' => '/uploads/factures/facture_intervention_2.pdf',
                'ttc' => 195.00, // 65€/h * 3h
                'created_at' => $now->copy()->subDays(25),
                'updated_at' => $now->copy()->subDays(25),
            ],
            [
                'intervention_id' => 3, // Client 4, Intervenant 9 - Ménage régulier (4h)
                'fichier_path' => '/uploads/factures/facture_intervention_3.pdf',
                'ttc' => 400.00, // 100€/h * 4h
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(20),
            ],
            [
                'intervention_id' => 4, // Client 5, Intervenant 10 - Deep cleaning (5.5h)
                'fichier_path' => '/uploads/factures/facture_intervention_4.pdf',
                'ttc' => 935.00, // 170€/h * 5.5h
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(15),
            ],
            [
                'intervention_id' => 5, // Client 2, Intervenant 11 - Plantation fleurs (3.5h)
                'fichier_path' => '/uploads/factures/facture_intervention_5.pdf',
                'ttc' => 385.00, // 110€/h * 3.5h
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ],
        ]);
    }
}
