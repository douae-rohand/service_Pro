<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Intervenant5TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $intervenantId = 5; // Amina Tazi (Gardener)
        $clientIds = [2, 3, 4, 9];
        $serviceId = 1; // Jardinage
        $tacheIdPelouse = 1; // Tonte de pelouse
        $tacheIdHaies = 2; // Taille de haies
        $tacheIdFleurs = 3; // Plantation de fleurs
        $tacheIdElagage = 4; // Élagage d'arbres

        // 1. Intervention: Completed & Fully Rated (15 days ago)
        $int1Id = DB::table('intervention')->insertGetId([
            'address' => '12 Rue des Oliviers, Fes',
            'ville' => 'Fes',
            'status' => 'termine',
            'date_intervention' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'client_id' => $clientIds[0],
            'intervenant_id' => $intervenantId,
            'tache_id' => $tacheIdPelouse,
            'created_at' => Carbon::now()->subDays(20),
            'updated_at' => Carbon::now()->subDays(15),
        ]);

        // Ratings for Int 1
        DB::table('evaluation')->insert([
            ['note' => 5, 'intervention_id' => $int1Id, 'critaire_id' => 1, 'type_auteur' => 'client', 'created_at' => Carbon::now()->subDays(14)],
            ['note' => 5, 'intervention_id' => $int1Id, 'critaire_id' => 1, 'type_auteur' => 'intervenant', 'created_at' => Carbon::now()->subDays(14)],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Travail impeccable, mon jardin n\'a jamais été aussi beau !', 'intervention_id' => $int1Id, 'type_auteur' => 'client', 'created_at' => Carbon::now()->subDays(14)],
            ['commentaire' => 'Client très aimable et jardin facile à entretenir.', 'intervention_id' => $int1Id, 'type_auteur' => 'intervenant', 'created_at' => Carbon::now()->subDays(14)],
        ]);

        // 2. Intervention: Completed (2 days ago), rated ONLY by Client (Should be PRIVATE)
        $int2Id = DB::table('intervention')->insertGetId([
            'address' => '45 Boulevard de la Liberté, Fes',
            'ville' => 'Fes',
            'status' => 'termine',
            'date_intervention' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'client_id' => $clientIds[1],
            'intervenant_id' => $intervenantId,
            'tache_id' => $tacheIdHaies,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        DB::table('evaluation')->insert([
            ['note' => 4, 'intervention_id' => $int2Id, 'critaire_id' => 1, 'type_auteur' => 'client', 'created_at' => Carbon::now()->subDays(1)],
        ]);
        DB::table('commentaire')->insert([
            ['commentaire' => 'Très pro, mais a oublié de ramasser quelques branches.', 'intervention_id' => $int2Id, 'type_auteur' => 'client', 'created_at' => Carbon::now()->subDays(1)],
        ]);

        // Add complementary info for Int 2
        DB::table('intervention_information')->insert([
            ['intervention_id' => $int2Id, 'information_id' => 4, 'information' => '120 m2', 'created_at' => now()], // Surface
            ['intervention_id' => $int2Id, 'information_id' => 5, 'information' => 'Moyenne', 'created_at' => now()], // Urgence
        ]);

        // 3. Intervention: Accepted (in 3 days)
        $int3Id = DB::table('intervention')->insertGetId([
            'address' => '78 Villa Jasmine, Fes',
            'ville' => 'Fes',
            'status' => 'acceptee',
            'date_intervention' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'client_id' => $clientIds[2],
            'intervenant_id' => $intervenantId,
            'tache_id' => $tacheIdFleurs,
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now(),
        ]);

        // Photos for Int 3 (Garden state)
        DB::table('photo_intervention')->insert([
            ['intervention_id' => $int3Id, 'photo_path' => 'demo/garden_1.jpg', 'description' => 'Espace à fleurir', 'phase_prise' => 'avant', 'created_at' => now()],
            ['intervention_id' => $int3Id, 'photo_path' => 'demo/garden_2.jpg', 'description' => 'Sol actuel', 'phase_prise' => 'avant', 'created_at' => now()],
        ]);

        // 4. Intervention: Pending (Next week)
        $int4Id = DB::table('intervention')->insertGetId([
            'address' => '8 Rue de la Gare, Fes',
            'ville' => 'Fes',
            'status' => 'en attend',
            'date_intervention' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'client_id' => $clientIds[3],
            'intervenant_id' => $intervenantId,
            'tache_id' => $tacheIdElagage,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Materials for Int 4
        DB::table('intervention_materiel')->insert([
            ['intervention_id' => $int4Id, 'materiel_id' => 1, 'created_at' => now()], // Assume 1 is a mower or saw
            ['intervention_id' => $int4Id, 'materiel_id' => 2, 'created_at' => now()],
        ]);

        // Info for Int 4
        DB::table('intervention_information')->insert([
            ['intervention_id' => $int4Id, 'information_id' => 4, 'information' => '300 m2', 'created_at' => now()],
            ['intervention_id' => $int4Id, 'information_id' => 5, 'information' => 'Urgente', 'created_at' => now()],
        ]);
    }
}
