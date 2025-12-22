<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('evaluation')->insert([
            // Client evaluation for intervention 1
            [
                'intervention_id' => 1,
                'critaire_id' => 1, // Qualité du travail
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 2, // Ponctualité
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 3, // Professionnalisme
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant evaluation for intervention 1
            [
                'intervention_id' => 1,
                'critaire_id' => 4, // Communication
                'note' => 5,
                'type_auteur' => 'intervenant',
                'is_public' => true, // Public - both parties rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Client evaluation for intervention 2
            [
                'intervention_id' => 2,
                'critaire_id' => 1, // Qualité du travail
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 2, // Ponctualité
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 5, // Propreté
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
