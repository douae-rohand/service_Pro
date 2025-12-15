<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterventionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('intervention')->insert([
            [
                'client_id' => 2,
                'intervenant_id' => 5,
                'tache_id' => 1, // Tonte de pelouse
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => '2025-12-05',
                'status' => 'termine',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 3,
                'intervenant_id' => 6,
                'tache_id' => 3, // Débroussaillage
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'date_intervention' => '2025-12-08',
                'status' => 'termine',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 4,
                'intervenant_id' => 7,
                'tache_id' => 6, // Ménage standard
                'address' => '12 Rue de la Liberté, Marrakech',
                'ville' => 'Marrakech',
                'date_intervention' => '2025-12-12',
                'status' => 'acceptee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 2,
                'intervenant_id' => 8,
                'tache_id' => 7, // Ménage en profondeur
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'date_intervention' => '2025-12-15',
                'status' => 'en attend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 3,
                'intervenant_id' => 5,
                'tache_id' => 2, // Taille de haies
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'date_intervention' => '2025-12-18',
                'status' => 'en attend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
