<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('client')->insert([
            [
                'id' => 2, // Mohammed Alami
                'address' => '45 Avenue Hassan II, Rabat',
                'ville' => 'Rabat',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3, // Fatima Benjelloun
                'address' => '78 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4, // Karim El Fassi
                'address' => '12 Rue de la Liberté, Marrakech',
                'ville' => 'Marrakech',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5, // Leila Idrissi
                'address' => '90 Rue Oued Fès, Rabat',
                'ville' => 'Rabat',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6, // Omar Mansouri
                'address' => '15 Avenue Allal Ben Abdellah, Fes',
                'ville' => 'Fes',
                'is_active' => true,
                'admin_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
