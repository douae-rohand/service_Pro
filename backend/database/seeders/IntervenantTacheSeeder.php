<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantTacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tâches Jardinage (1-6): 1=Tonte, 2=Taille, 3=Plantation, 4=Élagage, 5=Désherbage, 6=Potager
     * Tâches Ménage (7-12): 7=Ménage régulier, 8=Deep cleaning, 9=Déménagement, 10=Vitres, 11=Textiles, 12=Bureaux
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('intervenant_tache')->insert([
            // ============================================
            // Intervenant 7 (Amina Tazi) - Jardinage
            // ============================================
            [
                'intervenant_id' => 7,
                'tache_id' => 1, // Tonte de pelouse
                'prix_tache' => 80.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'tache_id' => 2, // Taille de haies
                'prix_tache' => 70.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'tache_id' => 3, // Plantation de fleurs
                'prix_tache' => 120.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'tache_id' => 4, // Élagage d'arbres
                'prix_tache' => 150.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'tache_id' => 5, // Désherbage
                'prix_tache' => 50.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 8 (Youssef Benslimane) - Jardinage
            // ============================================
            [
                'intervenant_id' => 8,
                'tache_id' => 1, // Tonte de pelouse
                'prix_tache' => 75.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'tache_id' => 2, // Taille de haies
                'prix_tache' => 65.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'tache_id' => 4, // Élagage d'arbres
                'prix_tache' => 140.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'tache_id' => 5, // Désherbage
                'prix_tache' => 45.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 9 (Sara Chraibi) - Ménage
            // ============================================
            [
                'intervenant_id' => 9,
                'tache_id' => 7, // Ménage résidentiel & régulier
                'prix_tache' => 100.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'tache_id' => 8, // Nettoyage en profondeur
                'prix_tache' => 180.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'tache_id' => 10, // Lavage vitres & surfaces spécialisées
                'prix_tache' => 60.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'tache_id' => 11, // Nettoyage mobilier & textiles
                'prix_tache' => 80.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 10 (Hassan Radi) - Ménage
            // ============================================
            [
                'intervenant_id' => 10,
                'tache_id' => 7, // Ménage résidentiel & régulier
                'prix_tache' => 95.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'tache_id' => 8, // Nettoyage en profondeur
                'prix_tache' => 170.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'tache_id' => 9, // Nettoyage spécial : déménagement & post-travaux
                'prix_tache' => 250.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'tache_id' => 12, // Nettoyage professionnel (bureaux & commerces)
                'prix_tache' => 120.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 11 (Ahmed Bennani) - Jardinage
            // ============================================
            [
                'intervenant_id' => 11,
                'tache_id' => 1, // Tonte de pelouse
                'prix_tache' => 70.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 11,
                'tache_id' => 3, // Plantation de fleurs
                'prix_tache' => 110.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 11,
                'tache_id' => 6, // Entretien de potager
                'prix_tache' => 90.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 12 (Khadija Alaoui) - Ménage
            // ============================================
            [
                'intervenant_id' => 12,
                'tache_id' => 7, // Ménage résidentiel & régulier
                'prix_tache' => 85.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 12,
                'tache_id' => 10, // Lavage vitres & surfaces spécialisées
                'prix_tache' => 55.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 13 (Mehdi Berrada) - Multi-service
            // ============================================
            // Jardinage
            [
                'intervenant_id' => 13,
                'tache_id' => 1, // Tonte de pelouse
                'prix_tache' => 65.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 13,
                'tache_id' => 5, // Désherbage
                'prix_tache' => 40.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Ménage
            [
                'intervenant_id' => 13,
                'tache_id' => 7, // Ménage résidentiel & régulier
                'prix_tache' => 90.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 15 (Samir Lahlou) - Jardinage
            // ============================================
            [
                'intervenant_id' => 15,
                'tache_id' => 1, // Tonte de pelouse
                'prix_tache' => 82.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'tache_id' => 2, // Taille de haies
                'prix_tache' => 72.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'tache_id' => 3, // Plantation de fleurs
                'prix_tache' => 125.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'tache_id' => 4, // Élagage d'arbres
                'prix_tache' => 155.00,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
