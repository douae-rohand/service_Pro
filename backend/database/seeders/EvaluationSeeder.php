<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Critères client (type='client'): IDs 1-5
     * Critères intervenant (type='intervenant'): IDs 6-11
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('evaluation')->insert([
            // ============================================
            // INTERVENTION 1 - Client 2, Intervenant 7 (Amina Tazi)
            // ============================================
            // Évaluations CLIENT (évalue intervenant)
            [
                'intervention_id' => 1,
                'critaire_id' => 6, // Qualité du travail
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 7, // Ponctualité
                'note' => 3, // Note réduite à cause du retard (cohérent avec la réclamation)
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 8, // Professionnalisme
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 9, // Communication
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 10, // Propreté
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => true, // Public - both parties rated
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            // Évaluations INTERVENANT (évalue client)
            [
                'intervention_id' => 1,
                'critaire_id' => 1, // Communication client
                'note' => 5,
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 2, // Précision des instructions
                'note' => 4,
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'critaire_id' => 3, // Disponibilité
                'note' => 5,
                'type_auteur' => 'intervenant',
                'is_public' => true, // Public - both parties rated
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            
            // ============================================
            // INTERVENTION 2 - Client 3, Intervenant 8 (Youssef Benslimane)
            // ============================================
            // Évaluations CLIENT
            [
                'intervention_id' => 2,
                'critaire_id' => 6, // Qualité du travail
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 7, // Ponctualité
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 8, // Professionnalisme
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 10, // Propreté
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 11, // Rapport qualité/prix
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            // Évaluations INTERVENANT
            [
                'intervention_id' => 2,
                'critaire_id' => 1, // Communication client
                'note' => 5,
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'critaire_id' => 4, // Respect du planning
                'note' => 3, // Note réduite à cause des annulations répétées (cohérent avec la réclamation)
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            
            // ============================================
            // INTERVENTION 3 - Client 4, Intervenant 9 (Sara Chraibi)
            // ============================================
            // Évaluations CLIENT
            [
                'intervention_id' => 3,
                'critaire_id' => 6, // Qualité du travail
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            [
                'intervention_id' => 3,
                'critaire_id' => 7, // Ponctualité
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            [
                'intervention_id' => 3,
                'critaire_id' => 8, // Professionnalisme
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            [
                'intervention_id' => 3,
                'critaire_id' => 9, // Communication
                'note' => 4, // Légèrement réduite à cause du problème de matériel
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            [
                'intervention_id' => 3,
                'critaire_id' => 10, // Propreté
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            
            // ============================================
            // INTERVENTION 4 - Client 5, Intervenant 10 (Hassan Radi)
            // ============================================
            // Évaluations CLIENT (note un peu plus basse pour varier)
            [
                'intervention_id' => 4,
                'critaire_id' => 6, // Qualité du travail
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            [
                'intervention_id' => 4,
                'critaire_id' => 7, // Ponctualité
                'note' => 3,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            [
                'intervention_id' => 4,
                'critaire_id' => 8, // Professionnalisme
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            [
                'intervention_id' => 4,
                'critaire_id' => 10, // Propreté
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            
            // ============================================
            // INTERVENTION 5 - Client 2, Intervenant 11 (Ahmed Bennani)
            // ============================================
            // Évaluations CLIENT
            [
                'intervention_id' => 5,
                'critaire_id' => 6, // Qualité du travail
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            [
                'intervention_id' => 5,
                'critaire_id' => 7, // Ponctualité
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            [
                'intervention_id' => 5,
                'critaire_id' => 8, // Professionnalisme
                'note' => 5,
                'type_auteur' => 'client',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            [
                'intervention_id' => 5,
                'critaire_id' => 11, // Rapport qualité/prix
                'note' => 4,
                'type_auteur' => 'client',
                'is_public' => false, // Private - only client rated
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            // Évaluations INTERVENANT (évalue client)
            [
                'intervention_id' => 5,
                'critaire_id' => 1, // Communication client
                'note' => 5,
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            [
                'intervention_id' => 5,
                'critaire_id' => 4, // Respect du planning (inclut le respect des délais de paiement)
                'note' => 2, // Note très basse à cause du paiement tardif (cohérent avec la réclamation)
                'type_auteur' => 'intervenant',
                'is_public' => false,
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
        ]);
    }
}
