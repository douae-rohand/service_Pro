<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JustificatifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Justificatifs pour les intervenants (IDs 7-15)
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('justificatif')->insert([
            // Intervenant 7 (Amina Tazi - Jardinière)
            [
                'intervenant_id' => 7,
                'type' => 'Certificat jardinage',
                'chemin_fichier' => '/uploads/justificatifs/cert_jardinage_7.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 7,
                'type' => 'Assurance professionnelle',
                'chemin_fichier' => '/uploads/justificatifs/assurance_7.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 8 (Youssef Benslimane - Jardinier)
            [
                'intervenant_id' => 8,
                'type' => 'Diplôme espaces verts',
                'chemin_fichier' => '/uploads/justificatifs/diplome_jardinage_8.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 8,
                'type' => 'Assurance professionnelle',
                'chemin_fichier' => '/uploads/justificatifs/assurance_8.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 9 (Sara Chraibi - Ménage)
            [
                'intervenant_id' => 9,
                'type' => 'Certificat hygiène et propreté',
                'chemin_fichier' => '/uploads/justificatifs/cert_menage_9.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 9,
                'type' => 'Formation nettoyage professionnel',
                'chemin_fichier' => '/uploads/justificatifs/formation_menage_9.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 10 (Hassan Radi - Nettoyeur)
            [
                'intervenant_id' => 10,
                'type' => 'Certificat nettoyage en profondeur',
                'chemin_fichier' => '/uploads/justificatifs/cert_nettoyage_10.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 10,
                'type' => 'Assurance professionnelle',
                'chemin_fichier' => '/uploads/justificatifs/assurance_10.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 11 (Ahmed Bennani - Jardinier)
            [
                'intervenant_id' => 11,
                'type' => 'Certificat jardinage',
                'chemin_fichier' => '/uploads/justificatifs/cert_jardinage_11.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 12 (Khadija Alaoui - Ménage)
            [
                'intervenant_id' => 12,
                'type' => 'Formation ménage résidentiel',
                'chemin_fichier' => '/uploads/justificatifs/formation_menage_12.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 13 (Mehdi Berrada - Multi-service)
            [
                'intervenant_id' => 13,
                'type' => 'Certificat jardinage',
                'chemin_fichier' => '/uploads/justificatifs/cert_jardinage_13.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 13,
                'type' => 'Certificat ménage',
                'chemin_fichier' => '/uploads/justificatifs/cert_menage_13.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Intervenant 15 (Samir Lahlou - Jardinier)
            [
                'intervenant_id' => 15,
                'type' => 'Diplôme aménagement paysager',
                'chemin_fichier' => '/uploads/justificatifs/diplome_paysage_15.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'intervenant_id' => 15,
                'type' => 'Assurance professionnelle',
                'chemin_fichier' => '/uploads/justificatifs/assurance_15.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
