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
                'status' => 'demmande',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 6 - Jardinage service (active)
            [
                'intervenant_id' => 6,
                'service_id' => 1, // Jardinage
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 7 - Ménage service (active)
            [
                'intervenant_id' => 7,
                'service_id' => 2, // Ménage
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Intervenant 8 - Ménage service (active)
            [
                'intervenant_id' => 8,
                'service_id' => 2, // Ménage
                'status' => 'refuse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Example: Intervenant 5 can also do Ménage (multi-service worker)
            [
                'intervenant_id' => 5,
                'service_id' => 2, // Ménage
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Example: Intervenant 6 temporarily stopped offering services
            [
                'intervenant_id' => 6,
                'service_id' => 2, // Ménage
                'status' => 'desactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
