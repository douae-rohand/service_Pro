<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('favorise')->insert([
            // Client 2 (Mohammed Alami) - Favoris
            [
                'client_id' => 2,
                'intervenant_id' => 7, // Amina Tazi
                'service_id' => 1, // Jardinage
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now->copy()->subDays(30),
            ],
            [
                'client_id' => 2,
                'intervenant_id' => 11, // Ahmed Bennani
                'service_id' => 1, // Jardinage
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(15),
            ],
            
            // Client 3 (Fatima Benjelloun) - Favoris
            [
                'client_id' => 3,
                'intervenant_id' => 8, // Youssef Benslimane
                'service_id' => 1, // Jardinage
                'created_at' => $now->copy()->subDays(25),
                'updated_at' => $now->copy()->subDays(25),
            ],
            [
                'client_id' => 3,
                'intervenant_id' => 9, // Sara Chraibi
                'service_id' => 2, // Ménage
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(20),
            ],
            
            // Client 4 (Karim El Fassi) - Favoris
            [
                'client_id' => 4,
                'intervenant_id' => 9, // Sara Chraibi
                'service_id' => 2, // Ménage
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            
            // Client 5 (Leila Idrissi) - Favoris
            [
                'client_id' => 5,
                'intervenant_id' => 10, // Hassan Radi
                'service_id' => 2, // Ménage
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            
            // Client 6 (Omar Mansouri) - Favoris
            [
                'client_id' => 6,
                'intervenant_id' => 15, // Samir Lahlou
                'service_id' => 1, // Jardinage
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ],
            [
                'client_id' => 6,
                'intervenant_id' => 12, // Khadija Alaoui
                'service_id' => 2, // Ménage
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(8),
            ],
        ]);
    }
}
