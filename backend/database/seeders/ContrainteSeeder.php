<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ContrainteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contrainte')->truncate();
        DB::table('contrainte')->insert([
            // ============================================
            // JARDINAGE TASKS
            // ============================================
            [
                'tache_id' => 1, // Tonte de pelouse
                'nom' => 'Surface maximale à tondre',
                'seuil' => 500.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 2, // Taille de haies
                'nom' => 'Hauteur maximale des haies',
                'seuil' => 3.00,
                'unite' => 'm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 3, // Plantation de fleurs
                'nom' => 'Surface maximale à planter',
                'seuil' => 100.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 4, // Élagage d'arbres
                'nom' => 'Hauteur maximale des arbres',
                'seuil' => 10.00,
                'unite' => 'm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 5, // Désherbage
                'nom' => 'Surface maximale à désherber',
                'seuil' => 300.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 6, // Entretien de potager
                'nom' => 'Surface maximale du potager',
                'seuil' => 150.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // MÉNAGE TASKS
            // ============================================
            [
                'tache_id' => 7, // Ménage résidentiel & régulier
                'nom' => 'Surface habitable maximale',
                'seuil' => 200.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 8, // Nettoyage en profondeur
                'nom' => 'Surface maximale à nettoyer',
                'seuil' => 250.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 9, // Nettoyage déménagement & post-travaux
                'nom' => 'Volume de déchets maximale',
                'seuil' => 10.00,
                'unite' => 'm³',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 10, // Lavage vitres & surfaces
                'nom' => 'Surface de vitrage maximale',
                'seuil' => 100.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 11, // Nettoyage mobilier & textiles
                'nom' => 'Nombre maximum de pièces textiles',
                'seuil' => 20.00,
                'unite' => 'pièces',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tache_id' => 12, // Nettoyage professionnel
                'nom' => 'Surface commerciale maximale',
                'seuil' => 500.00,
                'unite' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}