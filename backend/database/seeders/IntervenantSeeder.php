<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('intervenant')->insert([
            [
                'id' => 7, // Amina Tazi - Jardinière
                'address' => '34 Avenue Mohammed V, Fes',
                'ville' => 'Fes',
                'bio' => 'Jardinière professionnelle avec 8 ans d\'expérience en entretien de jardins et aménagement paysager. Passionnée par la nature et les espaces verts.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8, // Youssef Benslimane - Jardinier
                'address' => '56 Rue Ibn Battouta, Tanger',
                'ville' => 'Tanger',
                'bio' => 'Expert en jardinage avec spécialisation en taille d\'arbres et débroussaillage. Expérience de 10 ans dans l\'aménagement paysager.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9, // Sara Chraibi - Femme de ménage
                'address' => '89 Boulevard Anfa, Casablanca',
                'ville' => 'Casablanca',
                'bio' => 'Professionnelle du ménage avec 6 ans d\'expérience dans le nettoyage résidentiel et commercial. Méthodique et attentionnée.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10, // Hassan Radi - Nettoyeur spécialisé
                'address' => '23 Avenue des FAR, Agadir',
                'ville' => 'Agadir',
                'bio' => 'Spécialiste du ménage en profondeur et nettoyage après travaux. Plus de 7 ans d\'expérience dans le nettoyage professionnel.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11, // Ahmed Bennani - Jardinier
                'address' => '67 Rue Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'bio' => 'Jardinier expérimenté spécialisé dans l\'entretien régulier et la création de jardins. 5 ans d\'expérience.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12, // Khadija Alaoui - Femme de ménage
                'address' => '28 Avenue Mohammed VI, Rabat',
                'ville' => 'Rabat',
                'bio' => 'Professionnelle du ménage résidentiel avec une attention particulière aux détails. 4 ans d\'expérience.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13, // Mehdi Berrada - Jardinier/Ménage (multi-service)
                'address' => '45 Rue de France, Tanger',
                'ville' => 'Tanger',
                'bio' => 'Polyvalent, je propose des services de jardinage et de ménage. Flexible et disponible pour répondre à tous vos besoins.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14, // Nadia Fassi - Femme de ménage
                'address' => '12 Boulevard Mohammed V, Marrakech',
                'ville' => 'Marrakech',
                'bio' => 'Spécialiste en nettoyage en profondeur et entretien régulier. 8 ans d\'expérience avec des clients très satisfaits.',
                'is_active' => false, // Inactif pour tester le statut
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15, // Samir Lahlou - Jardinier
                'address' => '78 Avenue Hassan II, Agadir',
                'ville' => 'Agadir',
                'bio' => 'Expert en aménagement paysager et entretien de jardins. Formation professionnelle et 6 ans d\'expérience terrain.',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
