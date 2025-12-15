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
        DB::table('materiel')->insert([
            // Gardening equipment (service_id: 1)
            ['nom_materiel' => 'Tondeuse à gazon', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Taille-haie électrique', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Débroussailleuse', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Râteau et fourche', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Sécateur professionnel', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Arrosoir et tuyau', 'service_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Cleaning equipment (service_id: 2)
            ['nom_materiel' => 'Aspirateur industriel', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Nettoyeur vapeur', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Balai et serpillière', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Raclette vitres', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Fer à repasser', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nom_materiel' => 'Produits de nettoyage', 'service_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
