<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CritaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('critaire')->insert([
            // Intervenant evaluation criteria (for rating clients)
            ['nom_critaire' => 'Communication client', 'type' => 'client', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Précision des instructions', 'type' => 'client', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Disponibilité', 'type' => 'client', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Respect du planning', 'type' => 'client', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Clarté des besoins', 'type' => 'client', 'created_at' => now(), 'updated_at' => now()],
            // Client evaluation criteria (for rating intervenants)
            ['nom_critaire' => 'Qualité du travail', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Ponctualité', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Professionnalisme', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Communication', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Propreté', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
            ['nom_critaire' => 'Rapport qualité/prix', 'type' => 'intervenant', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
