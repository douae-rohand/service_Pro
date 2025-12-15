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
            // Intervenant 5 (Jardinière) can do gardening tasks
            ['intervenant_id' => 5, 'tache_id' => 1, 'prix_tache' => 35.0, 'status' => true], // Tonte
            ['intervenant_id' => 5, 'tache_id' => 2, 'prix_tache' => 40.0, 'status' => true], // Taille
            ['intervenant_id' => 5, 'tache_id' => 4, 'prix_tache' => 45.0, 'status' => false], // Aménagement
            ['intervenant_id' => 5, 'tache_id' => 5, 'prix_tache' => 25.0, 'status' => true], // Arrosage
            // Intervenant 6 (Jardinier) can do gardening tasks
            ['intervenant_id' => 6, 'tache_id' => 1, 'prix_tache' => 30.0, 'status' => true], // Tonte
            ['intervenant_id' => 6, 'tache_id' => 2, 'prix_tache' => 38.0, 'status' => true], // Taille
            ['intervenant_id' => 6, 'tache_id' => 3, 'prix_tache' => 42.0, 'status' => false], // Débroussaillage
            // Intervenant 7 (Ménage) can do cleaning tasks
            ['intervenant_id' => 7, 'tache_id' => 6, 'prix_tache' => 28.0, 'status' => true], // Ménage standard
            ['intervenant_id' => 7, 'tache_id' => 7, 'prix_tache' => 35.0, 'status' => false], // Ménage profondeur
            ['intervenant_id' => 7, 'tache_id' => 8, 'prix_tache' => 25.0, 'status' => true], // Vitres
            ['intervenant_id' => 7, 'tache_id' => 9, 'prix_tache' => 20.0, 'status' => false], // Repassage
            // Intervenant 8 (Ménage) can do cleaning tasks
            ['intervenant_id' => 8, 'tache_id' => 6, 'prix_tache' => 32.0, 'status' => true], // Ménage standard
            ['intervenant_id' => 8, 'tache_id' => 7, 'prix_tache' => 40.0, 'status' => true], // Ménage profondeur
            ['intervenant_id' => 8, 'tache_id' => 10, 'prix_tache' => 50.0, 'status' => false], // Après travaux
        ]);
    }
}
