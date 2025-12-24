<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisponibiliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('disponibilite')->insert([
            // ============================================
            // Intervenant 7 (Amina Tazi) - Jardinière
            // ============================================
            [
                'intervenant_id' => 7,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'type' => 'reguliere',
                'jours_semaine' => 'mardi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'type' => 'reguliere',
                'jours_semaine' => 'mercredi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'type' => 'ponctuelle',
                'jours_semaine' => null,
                'heure_debut' => '09:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => $now->copy()->addDays(10)->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 8 (Youssef Benslimane) - Jardinier
            // ============================================
            [
                'intervenant_id' => 8,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '07:00:00',
                'heure_fin' => '15:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'type' => 'reguliere',
                'jours_semaine' => 'jeudi',
                'heure_debut' => '07:00:00',
                'heure_fin' => '15:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'type' => 'reguliere',
                'jours_semaine' => 'vendredi',
                'heure_debut' => '07:00:00',
                'heure_fin' => '15:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'type' => 'reguliere',
                'jours_semaine' => 'samedi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '12:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 9 (Sara Chraibi) - Femme de ménage
            // ============================================
            [
                'intervenant_id' => 9,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'type' => 'reguliere',
                'jours_semaine' => 'mardi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'type' => 'reguliere',
                'jours_semaine' => 'mercredi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'type' => 'reguliere',
                'jours_semaine' => 'jeudi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'type' => 'ponctuelle',
                'jours_semaine' => null,
                'heure_debut' => '10:00:00',
                'heure_fin' => '14:00:00',
                'date_specific' => $now->copy()->addDays(7)->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 10 (Hassan Radi) - Nettoyeur
            // ============================================
            [
                'intervenant_id' => 10,
                'type' => 'reguliere',
                'jours_semaine' => 'mardi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '18:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'type' => 'reguliere',
                'jours_semaine' => 'mercredi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '18:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'type' => 'reguliere',
                'jours_semaine' => 'vendredi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '18:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 11 (Ahmed Bennani) - Jardinier
            // ============================================
            [
                'intervenant_id' => 11,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 11,
                'type' => 'reguliere',
                'jours_semaine' => 'mercredi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 11,
                'type' => 'reguliere',
                'jours_semaine' => 'vendredi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 11,
                'type' => 'reguliere',
                'jours_semaine' => 'samedi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '13:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 12 (Khadija Alaoui) - Femme de ménage
            // ============================================
            [
                'intervenant_id' => 12,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '10:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 12,
                'type' => 'reguliere',
                'jours_semaine' => 'jeudi',
                'heure_debut' => '10:00:00',
                'heure_fin' => '16:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 13 (Mehdi Berrada) - Multi-service
            // ============================================
            [
                'intervenant_id' => 13,
                'type' => 'reguliere',
                'jours_semaine' => 'mardi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 13,
                'type' => 'reguliere',
                'jours_semaine' => 'jeudi',
                'heure_debut' => '08:00:00',
                'heure_fin' => '17:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 13,
                'type' => 'reguliere',
                'jours_semaine' => 'samedi',
                'heure_debut' => '09:00:00',
                'heure_fin' => '15:00:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // ============================================
            // Intervenant 15 (Samir Lahlou) - Jardinier
            // ============================================
            [
                'intervenant_id' => 15,
                'type' => 'reguliere',
                'jours_semaine' => 'lundi',
                'heure_debut' => '07:30:00',
                'heure_fin' => '16:30:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'type' => 'reguliere',
                'jours_semaine' => 'mercredi',
                'heure_debut' => '07:30:00',
                'heure_fin' => '16:30:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'type' => 'reguliere',
                'jours_semaine' => 'vendredi',
                'heure_debut' => '07:30:00',
                'heure_fin' => '16:30:00',
                'date_specific' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'type' => 'ponctuelle',
                'jours_semaine' => null,
                'heure_debut' => '08:00:00',
                'heure_fin' => '12:00:00',
                'date_specific' => $now->copy()->addDays(14)->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
