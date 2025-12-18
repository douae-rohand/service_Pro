<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantMaterielSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('intervenant_materiel')->insert([
            // Intervenant 5 (Jardinier) - gardening tools
            ['intervenant_id' => 5, 'materiel_id' => 1, 'prix_materiel' => 50.00, 'created_at' => now(), 'updated_at' => now()], // Tondeuse
            ['intervenant_id' => 5, 'materiel_id' => 2, 'prix_materiel' => 30.00, 'created_at' => now(), 'updated_at' => now()], // Taille-haie
            ['intervenant_id' => 5, 'materiel_id' => 4, 'prix_materiel' => 15.00, 'created_at' => now(), 'updated_at' => now()], // Râteau
            ['intervenant_id' => 5, 'materiel_id' => 5, 'prix_materiel' => 20.00, 'created_at' => now(), 'updated_at' => now()], // Sécateur
            ['intervenant_id' => 5, 'materiel_id' => 6, 'prix_materiel' => 10.00, 'created_at' => now(), 'updated_at' => now()], // Arrosoir
            // Intervenant 6 (Jardinier) - gardening tools
            ['intervenant_id' => 6, 'materiel_id' => 1, 'prix_materiel' => 45.00, 'created_at' => now(), 'updated_at' => now()], // Tondeuse
            ['intervenant_id' => 6, 'materiel_id' => 2, 'prix_materiel' => 28.00, 'created_at' => now(), 'updated_at' => now()], // Taille-haie
            ['intervenant_id' => 6, 'materiel_id' => 3, 'prix_materiel' => 40.00, 'created_at' => now(), 'updated_at' => now()], // Débroussailleuse
            ['intervenant_id' => 6, 'materiel_id' => 5, 'prix_materiel' => 18.00, 'created_at' => now(), 'updated_at' => now()], // Sécateur
            // Intervenant 7 (Ménage) - cleaning tools
            ['intervenant_id' => 7, 'materiel_id' => 7, 'prix_materiel' => 25.00, 'created_at' => now(), 'updated_at' => now()], // Aspirateur
            ['intervenant_id' => 7, 'materiel_id' => 8, 'prix_materiel' => 35.00, 'created_at' => now(), 'updated_at' => now()], // Nettoyeur vapeur
            ['intervenant_id' => 7, 'materiel_id' => 9, 'prix_materiel' => 8.00, 'created_at' => now(), 'updated_at' => now()], // Balai
            ['intervenant_id' => 7, 'materiel_id' => 10, 'prix_materiel' => 12.00, 'created_at' => now(), 'updated_at' => now()], // Raclette vitres
            ['intervenant_id' => 7, 'materiel_id' => 11, 'prix_materiel' => 15.00, 'created_at' => now(), 'updated_at' => now()], // Fer à repasser
            // Intervenant 8 (Ménage) - cleaning tools
            ['intervenant_id' => 8, 'materiel_id' => 7, 'prix_materiel' => 22.00, 'created_at' => now(), 'updated_at' => now()], // Aspirateur
            ['intervenant_id' => 8, 'materiel_id' => 9, 'prix_materiel' => 7.00, 'created_at' => now(), 'updated_at' => now()], // Balai
            ['intervenant_id' => 8, 'materiel_id' => 12, 'prix_materiel' => 20.00, 'created_at' => now(), 'updated_at' => now()], // Produits nettoyage
        ]);
    }
}
