<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterielSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materiels = [
            // Gardening equipment (service_id: 1)
            ['nom_materiel' => 'Tondeuse à gazon', 'service_id' => 1, 'description' => 'Tondeuse performante pour tout type de terrain'],
            ['nom_materiel' => 'Taille-haie électrique', 'service_id' => 1, 'description' => 'Idéal pour les buissons et haies moyennes'],
            ['nom_materiel' => 'Débroussailleuse', 'service_id' => 1, 'description' => 'Pour les herbes hautes et les finitions'],
            ['nom_materiel' => 'Râteau et fourche', 'service_id' => 1, 'description' => 'Outils manuels essentiels'],
            ['nom_materiel' => 'Sécateur professionnel', 'service_id' => 1, 'description' => 'Coupe nette pour les rosiers et arbustes'],
            ['nom_materiel' => 'Arrosoir et tuyau', 'service_id' => 1, 'description' => 'Pour l\'arrosage manuel'],
            // Cleaning equipment (service_id: 2)
            ['nom_materiel' => 'Aspirateur industriel', 'service_id' => 2, 'description' => 'Puissant aspirateur eau et poussière'],
            ['nom_materiel' => 'Nettoyeur vapeur', 'service_id' => 2, 'description' => 'Désinfection sans produits chimiques'],
            ['nom_materiel' => 'Balai et serpillière', 'service_id' => 2, 'description' => 'Kit de base pour le lavage des sols'],
            ['nom_materiel' => 'Raclette vitres', 'service_id' => 2, 'description' => 'Pour des vitres sans traces'],
            ['nom_materiel' => 'Fer à repasser', 'service_id' => 2, 'description' => 'Centrale vapeur professionnelle'],
            ['nom_materiel' => 'Produits de nettoyage', 'service_id' => 2, 'description' => 'Gamme complète de produits éco-responsables'],
        ];

        foreach ($materiels as $materiel) {
            DB::table('materiel')->updateOrInsert(
                ['nom_materiel' => $materiel['nom_materiel']],
                [
                    'service_id' => $materiel['service_id'],
                    'description' => $materiel['description'],
                    'updated_at' => now(),
                    // created_at is handled by DB defaults if inserting
                ]
            );
        }
    }
}
