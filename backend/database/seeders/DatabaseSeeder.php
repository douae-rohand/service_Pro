<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Base tables (no dependencies)
        $this->call([
            UtilisateurSeeder::class,
            CritaireSeeder::class,
            InformationSeeder::class,
            ServiceSeeder::class,
            MaterielSeeder::class,
        ]);

        // User type tables (depend on utilisateur)
        $this->call([
            AdminSeeder::class,
            ClientSeeder::class,
            IntervenantSeeder::class,
        ]);

        // Service-related tables
        $this->call([
            TacheSeeder::class,
            JustificatifSeeder::class,
        ]);

        // Relationship tables
        $this->call([
            DisponibiliteSeeder::class,
            IntervenantServiceSeeder::class,
            IntervenantTacheSeeder::class,
            IntervenantMaterielSeeder::class,
            ServiceInformationSeeder::class,
            ServiceJustificatifSeeder::class,
            TacheMaterielSeeder::class,
            FavoriseSeeder::class,
            //DemoDataSeeder::class,
        ]);

        // Intervention and related data
        $this->call([
            InterventionSeeder::class,
            InterventionInformationSeeder::class,
            InterventionMaterielSeeder::class,
            PhotoInterventionSeeder::class,
            EvaluationSeeder::class,
            CommentaireSeeder::class,
            FactureSeeder::class,
        ]);
    }
}
