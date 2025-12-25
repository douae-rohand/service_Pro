<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveTestSeeder extends Seeder
{
    /**
     * Comprehensive test seeder for is_public functionality
     * 
     * This seeder creates 10 perfect test scenarios to test ALL aspects of the is_public column:
     * - Privacy protection (public vs private)
     * - 7-day expiration logic
     * - Both-parties-rated public visibility
     * - Admin statistics accuracy
     * - Client/Intervenant privacy
     */
    public function run(): void
    {
        $this->command->info('🌱 Creating comprehensive test data for is_public functionality...');

        // Get admin
        $adminId = DB::table('admin')->first()?->id ?? 1;
        
        // Get services
        $serviceMenage = DB::table('service')->where('nom_service', 'Ménage')->first();
        $serviceJardinage = DB::table('service')->where('nom_service', 'Jardinage')->first();
        
        if (!$serviceMenage || !$serviceJardinage) {
            $this->command->error('❌ Services not found. Run ServiceSeeder first.');
            return;
        }

        // Get taches
        $tacheMenage = DB::table('tache')->where('service_id', $serviceMenage->id)->first();
        $tacheJardinage = DB::table('tache')->where('service_id', $serviceJardinage->id)->first();
        
        // Get critaires
        $critaires = DB::table('critaire')->get();
        $critaireId = $critaires->first()?->id ?? 1;

        // ============================================
        // Get EXISTING Intervenant #5
        // ============================================
        $intervenant5 = DB::table('intervenant')->find(5);
        
        if (!$intervenant5) {
            $this->command->error('❌ Intervenant #5 not found. Please run IntervenantSeeder first.');
            $this->command->info('💡 Or create intervenant with ID 5 first.');
            return;
        }
        
        $intervenant5Id = $intervenant5->id;
        $this->command->info("✅ Using existing Intervenant #5 (ID: {$intervenant5Id}) for all test scenarios");

        // ============================================
        // Create Test Clients (for different scenarios)
        // ============================================
        $testClients = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $email = "test.client{$i}@ispublic.test";
            $user = DB::table('utilisateur')->where('email', $email)->first();
            
            if (!$user) {
                $userId = DB::table('utilisateur')->insertGetId([
                    'nom' => "TestClient{$i}",
                    'prenom' => "IsPublic",
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'telephone' => "060000000{$i}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::table('client')->insert([
                    'id' => $userId,
                    'address' => "100 Test Street #{$i}, Casablanca",
                    'ville' => 'Casablanca',
                    'is_active' => true,
                    'admin_id' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $userId = $user->id;
            }
            
            $testClients[$i] = $userId;
        }

        // ============================================
        // SCENARIO 1: No Evaluations Yet (Fresh Intervention)
        // ============================================
        $scenario1 = DB::table('intervention')->insertGetId([
            'address' => '100 Test Street',
            'ville' => 'Casablanca',
            'status' => 'termine',
            'date_intervention' => now()->subDays(2)->format('Y-m-d'),
            'client_id' => $testClients[1],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(2),
        ]);
        $this->command->info("✅ Scenario 1: No evaluations (ID: {$scenario1})");

        // ============================================
        // SCENARIO 2: Only Client Rated (PRIVATE - is_public=false)
        // ============================================
        $scenario2 = DB::table('intervention')->insertGetId([
            'address' => '200 Test Avenue',
            'ville' => 'Casablanca',  
            'status' => 'termine',
            'date_intervention' => now()->subDays(3)->format('Y-m-d'),
            'client_id' => $testClients[2],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(3),
        ]);
        DB::table('evaluation')->insert([
            'note' => 5,
            'intervention_id' => $scenario2,
            'critaire_id' => $critaireId,
            'type_auteur' => 'client',
            'is_public' => false, // ⚠️ PRIVATE - only client rated
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        DB::table('commentaire')->insert([
            'commentaire' => 'PRIVATE: This should NOT be visible yet - only client rated',
            'intervention_id' => $scenario2,
            'type_auteur' => 'client',
            'is_public' => false, // ⚠️ PRIVATE
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        $this->command->info("✅ Scenario 2: Only client rated - PRIVATE (ID: {$scenario2})");

        // ============================================
        // SCENARIO 3: Only Intervenant Rated (PRIVATE - is_public=false)
        // ============================================
        $scenario3 = DB::table('intervention')->insertGetId([
            'address' => '300 Test Boulevard',
            'ville' => 'Rabat',
            'status' => 'termine',
            'date_intervention' => now()->subDays(4)->format('Y-m-d'),
            'client_id' => $testClients[3],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(4),
        ]);
        DB::table('evaluation')->insert([
            'note' => 4,
            'intervention_id' => $scenario3,
            'critaire_id' => $critaireId,
            'type_auteur' => 'intervenant',
            'is_public' => false, // ⚠️ PRIVATE - only intervenant rated
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);
        $this->command->info("✅ Scenario 3: Only intervenant rated - PRIVATE (ID: {$scenario3})");

        // ============================================
        // SCENARIO 4: Both Rated (PUBLIC - is_public=true)
        // ============================================
        $scenario4 = DB::table('intervention')->insertGetId([
            'address' => '400 Test Street',
            'ville' => 'Marrakech',
            'status' => 'termine',
            'date_intervention' => now()->subDays(5)->format('Y-m-d'),
            'client_id' => $testClients[4],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(5),
        ]);
        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $scenario4, 'critaire_id' => $critaireId, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
            ['note' => 5, 'intervention_id' => $scenario4, 'critaire_id' => $critaireId, 'type_auteur' => 'intervenant', 'is_public' => true, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'PUBLIC: Both parties rated - this IS visible!', 'intervention_id' => $scenario4, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
            ['commentaire' => 'PUBLIC: Great client, professional service!', 'intervention_id' => $scenario4, 'type_auteur' => 'intervenant', 'is_public' => true, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
        ]);
        $this->command->info("✅ Scenario 4: Both rated - PUBLIC (ID: {$scenario4})");

        // ============================================
        // SCENARIO 5: Expired >7 Days (PUBLIC - is_public=true)
        // ============================================
        $scenario5 = DB::table('intervention')->insertGetId([
            'address' => '500 Test Avenue',
            'ville' => 'Fes',
            'status' => 'termine',
            'date_intervention' => now()->subDays(10)->format('Y-m-d'),
            'client_id' => $testClients[5],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(10),
        ]);
        DB::table('evaluation')->insert([
            'note' => 3,
            'intervention_id' => $scenario5,
            'critaire_id' => $critaireId,
            'type_auteur' => 'client',
            'is_public' => true, // ✅ PUBLIC - expired window
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);
        DB::table('commentaire')->insert([
            'commentaire' => 'PUBLIC: Revealed after 7 days - only client rated but visible now!',
            'intervention_id' => $scenario5,
            'type_auteur' => 'client',
            'is_public' => true, // ✅ PUBLIC - expired window
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);
        $this->command->info("✅ Scenario 5: Expired >7 days - PUBLIC (ID: {$scenario5})");

        // ============================================
        // SCENARIO 6: Expired, No Ratings
        // ============================================
        $scenario6 = DB::table('intervention')->insertGetId([
            'address' => '600 Test Boulevard',
            'ville' => 'Tangier',
            'status' => 'termine',
            'date_intervention' => now()->subDays(12)->format('Y-m-d'),
            'client_id' => $testClients[1],
            'intervenant_id' => $intervenant5Id,
            'tache_id' => $tacheMenage->id,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(12),
        ]);
        $this->command->info("✅ Scenario 6: Expired, no ratings (ID: {$scenario6})");

        // ============================================
        // SCENARIO 7-10: Mixed For Statistics Testing
        // ============================================
        // More public evaluations for intervenant 5 (good ratings)
        for ($i = 7; $i <= 10; $i++) {
            $interventionId = DB::table('intervention')->insertGetId([
                'address' => "{$i}00 Statistics Test St",
                'ville' => 'Casablanca',
                'status' => 'termine',
                'date_intervention' => now()->subDays(8 + $i)->format('Y-m-d'),
                'client_id' => $testClients[(($i % 5) + 1)],
                'intervenant_id' => $intervenant5Id,
                'tache_id' => $tacheMenage->id,
                'created_at' => now()->subDays(10 + $i),
                'updated_at' => now()->subDays(8 + $i),
            ]);
            
            DB::table('evaluation')->insert([
                ['note' => 5, 'intervention_id' => $interventionId, 'critaire_id' => $critaireId, 'type_auteur' => 'client', 'is_public' => true, 'created_at' => now()->subDays(7 + $i), 'updated_at' => now()->subDays(7 + $i)],
                ['note' => 4, 'intervention_id' => $interventionId, 'critaire_id' => $critaireId, 'type_auteur' => 'intervenant', 'is_public' => true, 'created_at' => now()->subDays(7 + $i), 'updated_at' => now()->subDays(7 + $i)],
            ]);
        }
        $this->command->info("✅ Scenarios 7-10: Statistics test data - PUBLIC");

        // ============================================
        // Summary
        // ============================================
        $this->command->info('');
        $this->command->info('📊 Comprehensive Test Data Created:');
        $this->command->info("   🎯 ALL scenarios use Intervenant #5 (ID: {$intervenant5Id})");
        $this->command->info("   • Scenario 1: Fresh intervention (no evaluations)");
        $this->command->info("   • Scenario 2: Only client rated (PRIVATE ❌)");
        $this->command->info("   • Scenario 3: Only intervenant rated (PRIVATE ❌)");
        $this->command->info("   • Scenario 4: Both rated (PUBLIC ✅)");
        $this->command->info("   • Scenario 5: Expired window (PUBLIC ✅)");
        $this->command->info("   • Scenario 6: Expired, no ratings");
        $this->command->info("   • Scenarios 7-10: Statistics test data (PUBLIC ✅)");
        $this->command->info('');
        $this->command->info('🧪 Test Coverage:');
        $this->command->info("   ✅ Privacy protection");
        $this->command->info("   ✅ 7-day expiration");
        $this->command->info("   ✅ Both-parties-rated visibility");
        $this->command->info("   ✅ Admin statistics accuracy");
        $this->command->info("   ✅ Client/Intervenant privacy");
        $this->command->info('');
        $this->command->info('💡 Login as Intervenant #5 to see ALL scenarios in one dashboard!');
    }
}
