<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantTacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('intervenant_tache')->insert([
            // Intervenant 5 (Jardinière)
            ['intervenant_id' => 5, 'tache_id' => 1, 'prix_tache' => 35.0, 'status' => true, 'prix_tache' => 80],  // Tonte
            ['intervenant_id' => 5, 'tache_id' => 2, 'prix_tache' => 40.0, 'status' => true, 'prix_tache' => 70],  // Taille
            ['intervenant_id' => 5, 'tache_id' => 4, 'prix_tache' => 45.0, 'status' => false, 'prix_tache' => 120], // Aménagement
            ['intervenant_id' => 5, 'tache_id' => 5, 'prix_tache' => 25.0, 'status' => true, 'prix_tache' => 50],  // Arrosage

            // Intervenant 6 (Jardinier)
            ['intervenant_id' => 6, 'tache_id' => 1, 'prix_tache' => 30.0, 'status' => true, 'prix_tache' => 75],  // Tonte
            ['intervenant_id' => 6, 'tache_id' => 2, 'prix_tache' => 38.0, 'status' => true, 'prix_tache' => 65],  // Taille
            ['intervenant_id' => 6, 'tache_id' => 3, 'prix_tache' => 42.0, 'status' => false, 'prix_tache' => 90],  // Débroussaillage

            // Intervenant 7 (Ménage)
            ['intervenant_id' => 7, 'tache_id' => 6, 'prix_tache' => 28.0, 'status' => true, 'prix_tache' => 100], // Ménage standard
            ['intervenant_id' => 7, 'tache_id' => 7, 'prix_tache' => 35.0, 'status' => false, 'prix_tache' => 150], // Ménage profondeur
            ['intervenant_id' => 7, 'tache_id' => 8, 'prix_tache' => 25.0, 'status' => true, 'prix_tache' => 60],  // Vitres
            ['intervenant_id' => 7, 'tache_id' => 9, 'prix_tache' => 20.0, 'status' => false, 'prix_tache' => 70],  // Repassage

            // Intervenant 8 (Ménage)
            ['intervenant_id' => 8, 'tache_id' => 6, 'prix_tache' => 32.0, 'status' => true, 'prix_tache' => 95],  // Ménage standard
            ['intervenant_id' => 8, 'tache_id' => 7, 'prix_tache' => 40.0, 'status' => true, 'prix_tache' => 140], // Ménage profondeur
            ['intervenant_id' => 8, 'tache_id' => 10, 'prix_tache' => 50.0, 'status' => false,'prix_tache' => 200], // Après travaux
        ]);
    }
}
