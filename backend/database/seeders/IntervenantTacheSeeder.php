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
            ['intervenant_id' => 5, 'tache_id' => 1, 'prix_tache' => 300.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Tonte
            ['intervenant_id' => 5, 'tache_id' => 2, 'prix_tache' => 200.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Taille
            ['intervenant_id' => 5, 'tache_id' => 4, 'prix_tache' => 400.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Aménagement
            ['intervenant_id' => 5, 'tache_id' => 5, 'prix_tache' => 150.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Arrosage
            // Intervenant 6 (Jardinier) can do gardening tasks
            ['intervenant_id' => 6, 'tache_id' => 1, 'prix_tache' => 280.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Tonte
            ['intervenant_id' => 6, 'tache_id' => 2, 'prix_tache' => 180.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Taille
            ['intervenant_id' => 6, 'tache_id' => 3, 'prix_tache' => 350.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Débroussaillage
            // Intervenant 7 (Ménage) can do cleaning tasks
            ['intervenant_id' => 7, 'tache_id' => 6, 'prix_tache' => 250.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Ménage standard
            ['intervenant_id' => 7, 'tache_id' => 7, 'prix_tache' => 450.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Ménage profondeur
            ['intervenant_id' => 7, 'tache_id' => 8, 'prix_tache' => 150.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Vitres
            ['intervenant_id' => 7, 'tache_id' => 9, 'prix_tache' => 120.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Repassage
            // Intervenant 8 (Ménage) can do cleaning tasks
            ['intervenant_id' => 8, 'tache_id' => 6, 'prix_tache' => 240.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Ménage standard
            ['intervenant_id' => 8, 'tache_id' => 7, 'prix_tache' => 420.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Ménage profondeur
            ['intervenant_id' => 8, 'tache_id' => 10, 'prix_tache' => 500.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()], // Après travaux
        ]);
    }
}
