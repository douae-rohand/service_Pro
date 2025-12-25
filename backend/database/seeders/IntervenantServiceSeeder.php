<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Statuts possibles: 'active', 'desactive', 'demmande', 'refuse', 'archive'
     */
    public function run(): void
    {
        DB::table('intervenant_service')->insert([
            // ============================================
            // JARDINAGE (Service ID = 1)
            // ============================================
            
            // Intervenant 7 (Amina Tazi) - Jardinage ACTIVE
            [
                'intervenant_id' => 7,
                'service_id' => 1,
                'status' => 'active',
                'presentation' => 'Jardinière professionnelle avec 8 ans d\'expérience. Le jardinage est ma passion depuis mon enfance. J\'ai choisi ce service pour partager mon amour des plantes et créer des espaces verts magnifiques qui apportent joie et sérénité.',
                'experience' => 8.00,
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],
            
            // Intervenant 8 (Youssef Benslimane) - Jardinage ACTIVE
            [
                'intervenant_id' => 8,
                'service_id' => 1,
                'status' => 'active',
                'presentation' => 'Expert en jardinage avec spécialisation en taille d\'arbres et débroussaillage. Passionné par la nature, je transforme les espaces extérieurs en havres de paix. 10 ans d\'expérience terrain.',
                'experience' => 10.00,
                'created_at' => now()->subMonths(8),
                'updated_at' => now(),
            ],
            
            // Intervenant 11 (Ahmed Bennani) - Jardinage ACTIVE
            [
                'intervenant_id' => 11,
                'service_id' => 1,
                'status' => 'active',
                'presentation' => 'Jardinier expérimenté spécialisé dans l\'entretien régulier et la création de jardins. Formation professionnelle et 5 ans d\'expérience. Disponible et réactif.',
                'experience' => 5.00,
                'created_at' => now()->subMonths(4),
                'updated_at' => now(),
            ],
            
            // Intervenant 15 (Samir Lahlou) - Jardinage ACTIVE
            [
                'intervenant_id' => 15,
                'service_id' => 1,
                'status' => 'active',
                'presentation' => 'Expert en aménagement paysager et entretien de jardins. Formation professionnelle complète et 6 ans d\'expérience terrain. Créatif et méticuleux.',
                'experience' => 6.00,
                'created_at' => now()->subMonths(5),
                'updated_at' => now(),
            ],
            
            // Intervenant 13 (Mehdi Berrada) - Jardinage DEMANDE (en attente)
            [
                'intervenant_id' => 13,
                'service_id' => 1,
                'status' => 'demmande',
                'presentation' => 'Nouveau sur la plateforme, je souhaite proposer mes services de jardinage. J\'ai une bonne expérience personnelle et je suis motivé à démarrer.',
                'experience' => 2.00,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            
            // ============================================
            // MÉNAGE (Service ID = 2)
            // ============================================
            
            // Intervenant 9 (Sara Chraibi) - Ménage ACTIVE
            [
                'intervenant_id' => 9,
                'service_id' => 2,
                'status' => 'active',
                'presentation' => 'Professionnelle du ménage avec 6 ans d\'expérience. Je suis passionnée par le ménage et j\'aime rendre les espaces propres et accueillants. Méthodique et attentionnée aux détails.',
                'experience' => 6.00,
                'created_at' => now()->subMonths(7),
                'updated_at' => now(),
            ],
            
            // Intervenant 10 (Hassan Radi) - Ménage ACTIVE
            [
                'intervenant_id' => 10,
                'service_id' => 2,
                'status' => 'active',
                'presentation' => 'Spécialiste du ménage en profondeur et nettoyage après travaux. Plus de 7 ans d\'expérience dans le nettoyage professionnel. Respectueux des délais et exigeant sur la qualité.',
                'experience' => 7.50,
                'created_at' => now()->subMonths(9),
                'updated_at' => now(),
            ],
            
            // Intervenant 12 (Khadija Alaoui) - Ménage ACTIVE
            [
                'intervenant_id' => 12,
                'service_id' => 2,
                'status' => 'active',
                'presentation' => 'Professionnelle du ménage résidentiel avec une attention particulière aux détails. 4 ans d\'expérience. Discrète, ponctuelle et fiable.',
                'experience' => 4.00,
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ],
            
            // Intervenant 13 (Mehdi Berrada) - Ménage ACTIVE (multi-service)
            [
                'intervenant_id' => 13,
                'service_id' => 2,
                'status' => 'active',
                'presentation' => 'Polyvalent, je propose également des services de ménage en complément du jardinage. Flexible et disponible pour répondre à tous les besoins de mes clients.',
                'experience' => 3.00,
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ],
            
            // Intervenant 7 (Amina Tazi) - Ménage DESACTIVE (test statut)
            [
                'intervenant_id' => 7,
                'service_id' => 2,
                'status' => 'desactive',
                'presentation' => 'J\'avais proposé des services de ménage en complément, mais j\'ai temporairement suspendu pour me concentrer sur le jardinage qui est ma spécialité principale.',
                'experience' => 2.00,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(30),
            ],
            
            // Intervenant 14 (Nadia Fassi) - Ménage REFUSE (test statut)
            [
                'intervenant_id' => 14,
                'service_id' => 2,
                'status' => 'refuse',
                'presentation' => 'Spécialiste en nettoyage en profondeur avec 8 ans d\'expérience. Malheureusement ma demande n\'a pas été acceptée.',
                'experience' => 8.00,
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subDays(15),
            ],
            
            // Intervenant 11 (Ahmed Bennani) - Ménage DEMANDE (en attente validation)
            [
                'intervenant_id' => 11,
                'service_id' => 2,
                'status' => 'demmande',
                'presentation' => 'Je souhaite élargir mes services en proposant également le ménage. J\'ai une expérience solide et je serai ravi de vous aider à maintenir votre domicile propre.',
                'experience' => 2.50,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ]);
    }
}