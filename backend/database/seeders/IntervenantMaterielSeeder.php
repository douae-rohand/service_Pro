<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantMaterielSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Matériels par intervenant (IDs 7-15)
     * Matériels Jardinage (1-6): Tondeuse, Taille-haie, Débroussailleuse, Râteau, Sécateur, Arrosoir
     * Matériels Ménage (7-12): Aspirateur, Nettoyeur vapeur, Balai, Raclette vitres, Fer à repasser, Produits nettoyage
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('intervenant_materiel')->insert([
            // ============================================
            // Intervenant 7 (Amina Tazi - Jardinière) - Outils jardinage
            // ============================================
            ['intervenant_id' => 7, 'materiel_id' => 1, 'prix_materiel' => 50.00, 'created_at' => $now, 'updated_at' => $now], // Tondeuse
            ['intervenant_id' => 7, 'materiel_id' => 2, 'prix_materiel' => 30.00, 'created_at' => $now, 'updated_at' => $now], // Taille-haie
            ['intervenant_id' => 7, 'materiel_id' => 4, 'prix_materiel' => 15.00, 'created_at' => $now, 'updated_at' => $now], // Râteau
            ['intervenant_id' => 7, 'materiel_id' => 5, 'prix_materiel' => 20.00, 'created_at' => $now, 'updated_at' => $now], // Sécateur
            ['intervenant_id' => 7, 'materiel_id' => 6, 'prix_materiel' => 10.00, 'created_at' => $now, 'updated_at' => $now], // Arrosoir
            
            // ============================================
            // Intervenant 8 (Youssef Benslimane - Jardinier) - Outils jardinage
            // ============================================
            ['intervenant_id' => 8, 'materiel_id' => 1, 'prix_materiel' => 45.00, 'created_at' => $now, 'updated_at' => $now], // Tondeuse
            ['intervenant_id' => 8, 'materiel_id' => 2, 'prix_materiel' => 28.00, 'created_at' => $now, 'updated_at' => $now], // Taille-haie
            ['intervenant_id' => 8, 'materiel_id' => 3, 'prix_materiel' => 40.00, 'created_at' => $now, 'updated_at' => $now], // Débroussailleuse
            ['intervenant_id' => 8, 'materiel_id' => 5, 'prix_materiel' => 18.00, 'created_at' => $now, 'updated_at' => $now], // Sécateur
            
            // ============================================
            // Intervenant 9 (Sara Chraibi - Ménage) - Outils ménage
            // ============================================
            ['intervenant_id' => 9, 'materiel_id' => 7, 'prix_materiel' => 25.00, 'created_at' => $now, 'updated_at' => $now], // Aspirateur
            ['intervenant_id' => 9, 'materiel_id' => 8, 'prix_materiel' => 35.00, 'created_at' => $now, 'updated_at' => $now], // Nettoyeur vapeur
            ['intervenant_id' => 9, 'materiel_id' => 9, 'prix_materiel' => 8.00, 'created_at' => $now, 'updated_at' => $now], // Balai
            ['intervenant_id' => 9, 'materiel_id' => 10, 'prix_materiel' => 12.00, 'created_at' => $now, 'updated_at' => $now], // Raclette vitres
            ['intervenant_id' => 9, 'materiel_id' => 11, 'prix_materiel' => 15.00, 'created_at' => $now, 'updated_at' => $now], // Fer à repasser
            
            // ============================================
            // Intervenant 10 (Hassan Radi - Nettoyeur) - Outils ménage
            // ============================================
            ['intervenant_id' => 10, 'materiel_id' => 7, 'prix_materiel' => 22.00, 'created_at' => $now, 'updated_at' => $now], // Aspirateur
            ['intervenant_id' => 10, 'materiel_id' => 8, 'prix_materiel' => 38.00, 'created_at' => $now, 'updated_at' => $now], // Nettoyeur vapeur
            ['intervenant_id' => 10, 'materiel_id' => 9, 'prix_materiel' => 7.00, 'created_at' => $now, 'updated_at' => $now], // Balai
            ['intervenant_id' => 10, 'materiel_id' => 12, 'prix_materiel' => 20.00, 'created_at' => $now, 'updated_at' => $now], // Produits nettoyage
            
            // ============================================
            // Intervenant 11 (Ahmed Bennani - Jardinier) - Outils jardinage
            // ============================================
            ['intervenant_id' => 11, 'materiel_id' => 1, 'prix_materiel' => 48.00, 'created_at' => $now, 'updated_at' => $now], // Tondeuse
            ['intervenant_id' => 11, 'materiel_id' => 2, 'prix_materiel' => 32.00, 'created_at' => $now, 'updated_at' => $now], // Taille-haie
            ['intervenant_id' => 11, 'materiel_id' => 6, 'prix_materiel' => 12.00, 'created_at' => $now, 'updated_at' => $now], // Arrosoir
            
            // ============================================
            // Intervenant 12 (Khadija Alaoui - Ménage) - Outils ménage
            // ============================================
            ['intervenant_id' => 12, 'materiel_id' => 7, 'prix_materiel' => 24.00, 'created_at' => $now, 'updated_at' => $now], // Aspirateur
            ['intervenant_id' => 12, 'materiel_id' => 9, 'prix_materiel' => 8.50, 'created_at' => $now, 'updated_at' => $now], // Balai
            ['intervenant_id' => 12, 'materiel_id' => 10, 'prix_materiel' => 11.00, 'created_at' => $now, 'updated_at' => $now], // Raclette vitres
            
            // ============================================
            // Intervenant 13 (Mehdi Berrada - Multi-service) - Mixte
            // ============================================
            ['intervenant_id' => 13, 'materiel_id' => 1, 'prix_materiel' => 46.00, 'created_at' => $now, 'updated_at' => $now], // Tondeuse (jardinage)
            ['intervenant_id' => 13, 'materiel_id' => 7, 'prix_materiel' => 23.00, 'created_at' => $now, 'updated_at' => $now], // Aspirateur (ménage)
            ['intervenant_id' => 13, 'materiel_id' => 9, 'prix_materiel' => 7.50, 'created_at' => $now, 'updated_at' => $now], // Balai (ménage)
            
            // ============================================
            // Intervenant 15 (Samir Lahlou - Jardinier) - Outils jardinage
            // ============================================
            ['intervenant_id' => 15, 'materiel_id' => 1, 'prix_materiel' => 52.00, 'created_at' => $now, 'updated_at' => $now], // Tondeuse
            ['intervenant_id' => 15, 'materiel_id' => 2, 'prix_materiel' => 35.00, 'created_at' => $now, 'updated_at' => $now], // Taille-haie
            ['intervenant_id' => 15, 'materiel_id' => 3, 'prix_materiel' => 42.00, 'created_at' => $now, 'updated_at' => $now], // Débroussailleuse
            ['intervenant_id' => 15, 'materiel_id' => 4, 'prix_materiel' => 16.00, 'created_at' => $now, 'updated_at' => $now], // Râteau
            ['intervenant_id' => 15, 'materiel_id' => 5, 'prix_materiel' => 22.00, 'created_at' => $now, 'updated_at' => $now], // Sécateur
        ]);
    }
}
