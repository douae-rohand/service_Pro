<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates 5 Scenarios for Intervenant 5 (Our Test User)
     */
    public function run(): void
    {
        $clientId = 2; // Target Client
        $me = 5;       // Current Intervenant (You)
        
        $this->command->info('Seeding Evaluation Scenarios for Intervenant ' . $me . '...');

        // Clean up previous runs
        // Optional: DB::table('intervention')->where('intervenant_id', $me)->delete();

        // 1. CASE 1: Standard Finished - No Ratings (Window < 7 days)
        // Should allow rating. No ratings visible.
        $case1 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 1 - Standard Finished',
            'ville' => 'Rabat',
            'date_intervention' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(1), // Finished 1 day ago
        ]);
        $this->command->info("Case 1 (Standard): Intervention ID $case1");

        // 2. CASE 2: Client Rated You, But You Haven't Rated (Private)
        // You should see "Client has rated" hidden message.
        $case2 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 2 - Client Rated Only',
            'ville' => 'Casablanca',
            'date_intervention' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subDays(2), // Finished 2 days ago
        ]);
        // Client rating
        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $case2, 'critaire_id' => 6, 'type_auteur' => 'client', 'is_public' => false, 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'HIDDEN COMMENT - You should not see this yet!', 'intervention_id' => $case2, 'type_auteur' => 'client', 'is_public' => false, 'created_at' => now()],
        ]);
        $this->command->info("Case 2 (Client Rated): Intervention ID $case2");

        // 3. CASE 3: Intervenant Rated, Client Has Not (Private)
        // You should see your own rating. Client sees nothing.
        $case3 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 3 - You Rated Only',
            'ville' => 'Marrakech',
            'date_intervention' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(3), // Finished 3 days ago
        ]);
        // Intervenant rating
        DB::table('evaluation')->insert([
            ['note' => 4, 'intervention_id' => $case3, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'is_public' => false, 'created_at' => now()],
        ]);
        $this->command->info("Case 3 (You Rated): Intervention ID $case3");

        // 4. CASE 4: Both Rated (Public)
        // Should see everything.
        $case4 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 4 - Fully Public',
            'ville' => 'Tanger',
            'date_intervention' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(4),
        ]);
        // Both ratings
        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $case4, 'critaire_id' => 6, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()],
            ['note' => 5, 'intervention_id' => $case4, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'is_public' => true, 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'PUBLIC COMMENT - Visible to all!', 'intervention_id' => $case4, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()],
            ['commentaire' => 'Also visible!', 'intervention_id' => $case4, 'type_auteur' => 'intervenant', 'is_public' => true, 'created_at' => now()],
        ]);
        $this->command->info("Case 4 (Both Rated): Intervention ID $case4");

        // 5. CASE 5: Window Expired (> 7 Days) - One Party Rated
        // Rating should be public due to time expiry.
        $case5 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 5 - Expired Window',
            'ville' => 'Agadir',
            'date_intervention' => Carbon::now()->subDays(9)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(9), // Finished 9 days ago (> 7 days)
        ]);
        // Only Client rated
        DB::table('evaluation')->insert([
            ['note' => 2, 'intervention_id' => $case5, 'critaire_id' => 6, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'EXPIRED REVEAL - Visible because > 7 days passed!', 'intervention_id' => $case5, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()],
        ]);
        $this->command->info("Case 5 (Expired): Intervention ID $case5");

        // 6. CASE 6: Window Expired - No Ratings
        // Should assume "Expired" state, no rating possible.
        $case6 = DB::table('intervention')->insertGetId([
            'client_id' => $clientId,
            'intervenant_id' => $me,
            'tache_id' => 1,
            'address' => 'Case 6 - Too Late',
            'ville' => 'Fes',
            'date_intervention' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'status' => 'termine',
            'created_at' => Carbon::now()->subDays(11),
            'updated_at' => Carbon::now()->subDays(10), // Finished 10 days ago
        ]);
        $this->command->info("Case 6 (Too Late): Intervention ID $case6");
    }
}
