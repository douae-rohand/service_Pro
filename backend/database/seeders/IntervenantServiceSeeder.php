<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('intervenant_service')->insert([
            // Intervenant 5 - Jardinage service (active)
            [
                'intervenant_id' => 5,
                'service_id' => 1, // Jardinage
                'status' => 'active',
                'presentation' => 'Le jardinage est ma passion depuis mon enfance. J\'ai choisi ce service pour partager mon amour des plantes et créer des espaces verts magnifiques qui apportent joie et sérénité aux personnes.',
                'experience' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 6 - Jardinage service (active)
            [
                'intervenant_id' => 6,
                'service_id' => 1, // Jardinage
                'status' => 'active',
                'presentation' => 'Passionné par la nature et le jardinage, j\'ai choisi ce service pour transformer les espaces extérieurs en havres de paix. Mon expérience me permet d\'offrir des solutions adaptées à chaque client.',
                'experience' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 7 - Ménage service (active)
            [
                'intervenant_id' => 7,
                'service_id' => 2, // Ménage
                'status' => 'active',
                'presentation' => 'Je suis passionnée par le ménage et j\'ai choisi ce service car j\'aime rendre les espaces propres et accueillants. Mon objectif est de fournir un service de qualité qui dépasse les attentes de mes clients.',
                'experience' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 8 - Ménage service (active)
            [
                'intervenant_id' => 8,
                'service_id' => 2, // Ménage
                'status' => 'active',
                'presentation' => 'Avec plusieurs années d\'expérience, j\'ai choisi le service de ménage car je crois qu\'un environnement propre est essentiel au bien-être. Je m\'engage à fournir un service professionnel et méticuleux.',
                'experience' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Example: Intervenant 5 can also do Ménage (multi-service worker)
            [
                'intervenant_id' => 5,
                'service_id' => 2, // Ménage
                'status' => 'active',
                'presentation' => 'En plus du jardinage, j\'offre également des services de ménage. J\'ai choisi d\'élargir mes services pour répondre aux besoins complets de mes clients et leur offrir un service complet pour leur domicile.',
                'experience' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Example: Intervenant 6 temporarily stopped offering services
            [
                'intervenant_id' => 6,
                'service_id' => 2, // Ménage
                'status' => 'desactive',
                'presentation' => 'J\'avais choisi d\'offrir également des services de ménage pour compléter mon offre, mais j\'ai temporairement suspendu ce service pour me concentrer sur le jardinage.',
                'experience' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
