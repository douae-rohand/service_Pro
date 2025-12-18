<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Ce seeder ajoute des données de test supplémentaires pour tester
     * toutes les fonctionnalités de l'espace admin, notamment:
     * - Les clients avec emails spécifiques (différents des autres seeders)
     * - Les intervenants avec emails spécifiques (différents des autres seeders)
     * - Les interventions, évaluations, factures, réclamations
     * 
     * IMPORTANT: Ce seeder utilise des emails et données UNIQUES qui ne sont PAS
     * utilisées dans les autres seeders pour éviter les conflits.
     * 
     * Ce seeder est conçu pour être idempotent : il peut être exécuté plusieurs fois
     * sans créer de doublons. Il vérifie l'existence des données avant de les créer.
     */
    public function run(): void
    {
        // ============================================
        // 0. Récupérer l'admin existant (créé par AdminSeeder)
        // ============================================
        $admin = DB::table('admin')->first();
        if (!$admin) {
            // Si aucun admin n'existe, essayer de trouver l'utilisateur admin
            $adminUser = DB::table('utilisateur')->where('email', 'admin@servicepro.com')->first();
            if ($adminUser) {
                DB::table('admin')->insert([
                    'id' => $adminUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $adminId = $adminUser->id;
            } else {
                $this->command->warn('⚠️  Aucun admin trouvé. Utilisation de l\'ID 1 par défaut.');
                $adminId = 1;
            }
        } else {
            $adminId = $admin->id;
        }

        // ============================================
        // 1. Créer les utilisateurs pour les clients de test
        // ============================================
        // Emails uniques qui ne sont PAS dans UtilisateurSeeder
        $client1User = DB::table('utilisateur')->where('email', 'tafraoutist@gmail.com')->first();
        $client2User = DB::table('utilisateur')->where('email', 'tafraouti.aya2006@gmail.com')->first();
        $client3User = DB::table('utilisateur')->where('email', 'test.client3@example.com')->first();
        
        if (!$client1User) {
            $client1UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Alaoui',
                'prenom' => 'Soufiane',
                'email' => 'alaouis@gmail.com',
                'password' => Hash::make('password123'),
                'telephone' => '0611122233',
                'url' => null,
                'google_pw' => null,
                'address' => '100 Rue Mohammed V, Casablanca',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        } else {
            $client1UserId = $client1User->id;
        }
        
        if (!$client2User) {
            $client2UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Ayadi',
                'prenom' => 'Aya',
                'email' => 'ayadi.aya@gmail.com',
                'password' => Hash::make('password123'),
                'telephone' => '0612233344',
                'url' => null,
                'google_pw' => null,
                'address' => '150 Boulevard Zerktouni, Rabat',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        } else {
            $client2UserId = $client2User->id;
        }

        if (!$client3User) {
            $client3UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Test',
                'prenom' => 'Client3',
                'email' => 'test.client3@example.com',
                'password' => Hash::make('password123'),
                'telephone' => '0613344455',
                'url' => null,
                'google_pw' => null,
                'address' => '200 Avenue Allal Ben Abdellah, Marrakech',
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subMonths(1),
            ]);
        } else {
            $client3UserId = $client3User->id;
        }

        // ============================================
        // 2. Créer les clients
        // ============================================
        if (!DB::table('client')->where('id', $client1UserId)->exists()) {
            DB::table('client')->insert([
                'id' => $client1UserId,
                'address' => '100 Rue Mohammed V, Casablanca',
                'ville' => 'Casablanca',
                'is_active' => true,
                'admin_id' => $adminId,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        }
        
        if (!DB::table('client')->where('id', $client2UserId)->exists()) {
            DB::table('client')->insert([
                'id' => $client2UserId,
                'address' => '150 Boulevard Zerktouni, Rabat',
                'ville' => 'Rabat',
                'is_active' => true,
                'admin_id' => $adminId,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        if (!DB::table('client')->where('id', $client3UserId)->exists()) {
            DB::table('client')->insert([
                'id' => $client3UserId,
                'address' => '200 Avenue Allal Ben Abdellah, Marrakech',
                'ville' => 'Marrakech',
                'is_active' => false, // Client suspendu pour tester
                'admin_id' => $adminId,
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subMonths(1),
            ]);
        }

        // ============================================
        // 3. Créer les utilisateurs pour les intervenants de test
        // ============================================
        // Emails uniques qui ne sont PAS dans UtilisateurSeeder
        $intervenant1User = DB::table('utilisateur')->where('email', 'tafraouti.sanae@etu.uae.ac.ma')->first();
        $intervenant2User = DB::table('utilisateur')->where('email', 'tafraouti.khaoula@gmail.com')->first();
        $intervenant3User = DB::table('utilisateur')->where('email', 'test.intervenant3@example.com')->first();
        
        if (!$intervenant1User) {
            $intervenant1UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Benjelloun',
                'prenom' => 'Sare',
                'email' => 'benjelloun.sara@gmail.com',
                'password' => Hash::make('password123'),
                'telephone' => '0623344455',
                'url' => null,
                'google_pw' => null,
                'address' => '250 Avenue Hassan II, Marrakech',
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ]);
        } else {
            $intervenant1UserId = $intervenant1User->id;
        }
        
        if (!$intervenant2User) {
            $intervenant2UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Hamani',
                'prenom' => 'Adil',
                'email' => 'hamani.adil@gmail.com',
                'password' => Hash::make('password123'),
                'telephone' => '0624455566',
                'url' => null,
                'google_pw' => null,
                'address' => '300 Rue Ibn Zohr, Fes',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        } else {
            $intervenant2UserId = $intervenant2User->id;
        }

        if (!$intervenant3User) {
            $intervenant3UserId = DB::table('utilisateur')->insertGetId([
                'nom' => 'Test',
                'prenom' => 'Intervenant3',
                'email' => 'test.intervenant3@example.com',
                'password' => Hash::make('password123'),
                'telephone' => '0625566677',
                'url' => null,
                'google_pw' => null,
                'address' => '350 Boulevard Zerktouni, Casablanca',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        } else {
            $intervenant3UserId = $intervenant3User->id;
        }

        // ============================================
        // 4. Créer les intervenants
        // ============================================
        if (!DB::table('intervenant')->where('id', $intervenant1UserId)->exists()) {
            DB::table('intervenant')->insert([
                'id' => $intervenant1UserId,
                'address' => '250 Avenue Hassan II, Marrakech',
                'ville' => 'Marrakech',
                'bio' => 'Professionnelle du ménage avec 5 ans d\'expérience. Spécialisée dans le nettoyage résidentiel et commercial de haute qualité.',
                'is_active' => true,
                'admin_id' => $adminId,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ]);
        }
        
        if (!DB::table('intervenant')->where('id', $intervenant2UserId)->exists()) {
            DB::table('intervenant')->insert([
                'id' => $intervenant2UserId,
                'address' => '300 Rue Ibn Zohr, Fes',
                'ville' => 'Fes',
                'bio' => 'Experte en jardinage et aménagement paysager. Passionnée par la création d\'espaces verts magnifiques et durables.',
                'is_active' => false, // En attente d'approbation pour tester les demandes
                'admin_id' => null,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        }

        if (!DB::table('intervenant')->where('id', $intervenant3UserId)->exists()) {
            DB::table('intervenant')->insert([
                'id' => $intervenant3UserId,
                'address' => '350 Boulevard Zerktouni, Casablanca',
                'ville' => 'Casablanca',
                'bio' => 'Spécialiste en nettoyage en profondeur et après travaux. Plus de 10 ans d\'expérience.',
                'is_active' => true,
                'admin_id' => $adminId,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        // ============================================
        // 5. Récupérer les services existants (créés par ServiceSeeder)
        // ============================================
        $serviceJardinage = DB::table('service')->where('nom_service', 'Jardinage')->first();
        $serviceMenage = DB::table('service')->where('nom_service', 'Ménage')->first();
        
        if (!$serviceJardinage || !$serviceMenage) {
            $this->command->warn('⚠️  Les services Jardinage et Ménage doivent être créés par ServiceSeeder avant TestDataSeeder.');
            return;
        }
        
        $serviceJardinageId = $serviceJardinage->id;
        $serviceMenageId = $serviceMenage->id;

        // ============================================
        // 6. Associer les intervenants aux services
        // ============================================
        // Intervenant 1 - Ménage (active)
        if (!DB::table('intervenant_service')->where('intervenant_id', $intervenant1UserId)->where('service_id', $serviceMenageId)->exists()) {
            DB::table('intervenant_service')->insert([
                'intervenant_id' => $intervenant1UserId,
                'service_id' => $serviceMenageId,
                'status' => 'active',
                'experience' => 5,
                'presentation' => 'Je suis passionnée par le ménage et j\'ai choisi ce service car j\'aime rendre les espaces propres et accueillants. Mon objectif est de fournir un service de qualité qui dépasse les attentes de mes clients.',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        }
        
        // Intervenant 2 - Jardinage (demande en attente)
        if (!DB::table('intervenant_service')->where('intervenant_id', $intervenant2UserId)->where('service_id', $serviceJardinageId)->exists()) {
            DB::table('intervenant_service')->insert([
                'intervenant_id' => $intervenant2UserId,
                'service_id' => $serviceJardinageId,
                'status' => 'demmande',
                'experience' => 7,
                'presentation' => 'Le jardinage est ma passion depuis mon enfance. J\'ai choisi ce service pour partager mon amour des plantes et créer des espaces verts magnifiques qui apportent joie et sérénité aux personnes.',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        // Intervenant 3 - Ménage (active)
        if (!DB::table('intervenant_service')->where('intervenant_id', $intervenant3UserId)->where('service_id', $serviceMenageId)->exists()) {
            DB::table('intervenant_service')->insert([
                'intervenant_id' => $intervenant3UserId,
                'service_id' => $serviceMenageId,
                'status' => 'active',
                'experience' => 10,
                'presentation' => 'Avec plus de 10 ans d\'expérience, j\'ai choisi le service de ménage car je crois qu\'un environnement propre est essentiel au bien-être. Je m\'engage à fournir un service professionnel et méticuleux.',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        // ============================================
        // 7. Récupérer les tâches existantes (créées par TacheSeeder)
        // ============================================
        // Utiliser les tâches existantes du service Ménage
        $tacheMenageResidentiel = DB::table('tache')->where('service_id', $serviceMenageId)->where('nom_tache', 'like', '%Ménage résidentiel%')->first();
        $tacheNettoyageProfondeur = DB::table('tache')->where('service_id', $serviceMenageId)->where('nom_tache', 'like', '%Nettoyage en profondeur%')->first();
        $tacheLavageVitres = DB::table('tache')->where('service_id', $serviceMenageId)->where('nom_tache', 'like', '%Lavage vitres%')->first();
        
        // Utiliser les tâches existantes du service Jardinage
        $tacheTonte = DB::table('tache')->where('service_id', $serviceJardinageId)->where('nom_tache', 'like', '%Tonte%')->first();
        $tacheTaille = DB::table('tache')->where('service_id', $serviceJardinageId)->where('nom_tache', 'like', '%Taille%')->first();

        if (!$tacheMenageResidentiel || !$tacheNettoyageProfondeur) {
            $this->command->warn('⚠️  Les tâches de ménage doivent être créées par TacheSeeder avant TestDataSeeder.');
            return;
        }

        $tacheMenageResidentielId = $tacheMenageResidentiel->id;
        $tacheNettoyageProfondeurId = $tacheNettoyageProfondeur->id;
        $tacheLavageVitresId = $tacheLavageVitres ? $tacheLavageVitres->id : $tacheMenageResidentielId;
        $tacheTonteId = $tacheTonte ? $tacheTonte->id : null;
        $tacheTailleId = $tacheTaille ? $tacheTaille->id : null;

        // ============================================
        // 8. Associer les tâches aux intervenants avec prix
        // ============================================
        $prixMenageResidentiel = 250.00;
        $prixNettoyageProfondeur = 450.00;
        $prixLavageVitres = 150.00;
        $prixTonte = 300.00;
        $prixTaille = 200.00;
        
        // Intervenant 1 - Tâches ménage
        if (!DB::table('intervenant_tache')->where('intervenant_id', $intervenant1UserId)->where('tache_id', $tacheMenageResidentielId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant1UserId,
                'tache_id' => $tacheMenageResidentielId,
                'prix_tache' => $prixMenageResidentiel,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        }

        if (!DB::table('intervenant_tache')->where('intervenant_id', $intervenant1UserId)->where('tache_id', $tacheNettoyageProfondeurId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant1UserId,
                'tache_id' => $tacheNettoyageProfondeurId,
                'prix_tache' => $prixNettoyageProfondeur,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]);
        }

        // Intervenant 2 - Tâches jardinage (si disponibles)
        if ($tacheTonteId && !DB::table('intervenant_tache')->where('intervenant_id', $intervenant2UserId)->where('tache_id', $tacheTonteId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant2UserId,
                'tache_id' => $tacheTonteId,
                'prix_tache' => $prixTonte,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        if ($tacheTailleId && !DB::table('intervenant_tache')->where('intervenant_id', $intervenant2UserId)->where('tache_id', $tacheTailleId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant2UserId,
                'tache_id' => $tacheTailleId,
                'prix_tache' => $prixTaille,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        // Intervenant 3 - Tâches ménage
        if (!DB::table('intervenant_tache')->where('intervenant_id', $intervenant3UserId)->where('tache_id', $tacheMenageResidentielId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant3UserId,
                'tache_id' => $tacheMenageResidentielId,
                'prix_tache' => $prixMenageResidentiel,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        if ($tacheLavageVitresId && !DB::table('intervenant_tache')->where('intervenant_id', $intervenant3UserId)->where('tache_id', $tacheLavageVitresId)->exists()) {
            DB::table('intervenant_tache')->insert([
                'intervenant_id' => $intervenant3UserId,
                'tache_id' => $tacheLavageVitresId,
                'prix_tache' => $prixLavageVitres,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]);
        }

        // ============================================
        // 9. Créer des justificatifs pour les intervenants
        // ============================================
        $justificatifs = [
            [
                'type' => 'CNI',
                'chemin_fichier' => 'justificatifs/intervenant_' . $intervenant1UserId . '_cni.pdf',
                'intervenant_id' => $intervenant1UserId,
                'months_ago' => 4,
            ],
            [
                'type' => 'Diplôme',
                'chemin_fichier' => 'justificatifs/intervenant_' . $intervenant1UserId . '_diplome.pdf',
                'intervenant_id' => $intervenant1UserId,
                'months_ago' => 4,
            ],
            [
                'type' => 'CNI',
                'chemin_fichier' => 'justificatifs/intervenant_' . $intervenant2UserId . '_cni.pdf',
                'intervenant_id' => $intervenant2UserId,
                'months_ago' => 3,
            ],
            [
                'type' => 'Certificat professionnel',
                'chemin_fichier' => 'justificatifs/intervenant_' . $intervenant3UserId . '_cert.pdf',
                'intervenant_id' => $intervenant3UserId,
                'months_ago' => 2,
            ],
        ];
        
        foreach ($justificatifs as $justificatif) {
            $monthsAgo = $justificatif['months_ago'];
            unset($justificatif['months_ago']);
            
            if (!DB::table('justificatif')->where('intervenant_id', $justificatif['intervenant_id'])->where('type', $justificatif['type'])->exists()) {
                DB::table('justificatif')->insert(array_merge($justificatif, [
                    'created_at' => now()->subMonths($monthsAgo),
                    'updated_at' => now()->subMonths($monthsAgo),
                ]));
            }
        }

        // ============================================
        // 10. Créer des interventions
        // ============================================
        // Intervention 1 - Terminée
        $intervention1 = DB::table('intervention')
            ->where('client_id', $client1UserId)
            ->where('intervenant_id', $intervenant1UserId)
            ->where('date_intervention', now()->subDays(15)->format('Y-m-d'))
            ->first();
        
        if (!$intervention1) {
            $intervention1Id = DB::table('intervention')->insertGetId([
                'address' => '100 Rue Mohammed V, Casablanca',
                'ville' => 'Casablanca',
                'status' => 'termine',
                'date_intervention' => now()->subDays(15)->format('Y-m-d'),
                'client_id' => $client1UserId,
                'intervenant_id' => $intervenant1UserId,
                'tache_id' => $tacheMenageResidentielId,
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(15),
            ]);
        } else {
            $intervention1Id = $intervention1->id;
        }

        // Intervention 2 - Terminée
        $intervention2 = DB::table('intervention')
            ->where('client_id', $client2UserId)
            ->where('intervenant_id', $intervenant1UserId)
            ->where('date_intervention', now()->subDays(10)->format('Y-m-d'))
            ->first();
        
        if (!$intervention2) {
            $intervention2Id = DB::table('intervention')->insertGetId([
                'address' => '150 Boulevard Zerktouni, Rabat',
                'ville' => 'Rabat',
                'status' => 'termine',
                'date_intervention' => now()->subDays(10)->format('Y-m-d'),
                'client_id' => $client2UserId,
                'intervenant_id' => $intervenant1UserId,
                'tache_id' => $tacheNettoyageProfondeurId,
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(10),
            ]);
        } else {
            $intervention2Id = $intervention2->id;
        }

        // Intervention 3 - En cours
        $intervention3 = DB::table('intervention')
            ->where('client_id', $client1UserId)
            ->where('intervenant_id', $intervenant1UserId)
            ->where('date_intervention', now()->addDays(5)->format('Y-m-d'))
            ->first();
        
        if (!$intervention3) {
            $intervention3Id = DB::table('intervention')->insertGetId([
                'address' => '100 Rue Mohammed V, Casablanca',
                'ville' => 'Casablanca',
                'status' => 'acceptee',
                'date_intervention' => now()->addDays(5)->format('Y-m-d'),
                'client_id' => $client1UserId,
                'intervenant_id' => $intervenant1UserId,
                'tache_id' => $tacheMenageResidentielId,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]);
        } else {
            $intervention3Id = $intervention3->id;
        }

        // Intervention 4 - Annulée (pour tester les différents statuts)
        $intervention4 = DB::table('intervention')
            ->where('client_id', $client3UserId)
            ->where('date_intervention', now()->subDays(7)->format('Y-m-d'))
            ->first();
        
        if (!$intervention4) {
            $intervention4Id = DB::table('intervention')->insertGetId([
                'address' => '200 Avenue Allal Ben Abdellah, Marrakech',
                'ville' => 'Marrakech',
                'status' => 'termine',
                'date_intervention' => now()->subDays(7)->format('Y-m-d'),
                'client_id' => $client3UserId,
                'intervenant_id' => $intervenant3UserId,
                'tache_id' => $tacheMenageResidentielId,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(7),
            ]);
        } else {
            $intervention4Id = $intervention4->id;
        }

        // ============================================
        // 11. Créer des évaluations (pour tester les statistiques)
        // ============================================
        // Récupérer un critère existant
        $critaireId = DB::table('critaire')->first()?->id;
        
        if ($critaireId) {
            // Évaluations client pour intervention 1
            $evaluations1 = [
                ['note' => 5, 'type_auteur' => 'client'],
                ['note' => 4, 'type_auteur' => 'client'],
            ];
            
            foreach ($evaluations1 as $eval) {
                if (!DB::table('evaluation')->where('intervention_id', $intervention1Id)->where('type_auteur', $eval['type_auteur'])->where('note', $eval['note'])->exists()) {
                    DB::table('evaluation')->insert([
                        'note' => $eval['note'],
                        'intervention_id' => $intervention1Id,
                        'critaire_id' => $critaireId,
                        'type_auteur' => $eval['type_auteur'],
                        'created_at' => now()->subDays(14),
                        'updated_at' => now()->subDays(14),
                    ]);
                }
            }

            // Évaluation client pour intervention 2
            if (!DB::table('evaluation')->where('intervention_id', $intervention2Id)->where('type_auteur', 'client')->exists()) {
                DB::table('evaluation')->insert([
                    'note' => 5,
                    'intervention_id' => $intervention2Id,
                    'critaire_id' => $critaireId,
                    'type_auteur' => 'client',
                    'created_at' => now()->subDays(9),
                    'updated_at' => now()->subDays(9),
                ]);
            }

            // Évaluation intervenant (optionnel)
            if (!DB::table('evaluation')->where('intervention_id', $intervention1Id)->where('type_auteur', 'intervenant')->exists()) {
                DB::table('evaluation')->insert([
                    'note' => 5,
                    'intervention_id' => $intervention1Id,
                    'critaire_id' => $critaireId,
                    'type_auteur' => 'intervenant',
                    'created_at' => now()->subDays(13),
                    'updated_at' => now()->subDays(13),
                ]);
            }
        }

        // ============================================
        // 12. Créer des commentaires
        // ============================================
        $commentaires = [
            [
                'commentaire' => 'Excellent service ! Le ménage était parfaitement fait. Je recommande vivement.',
                'intervention_id' => $intervention1Id,
                'type_auteur' => 'client',
                'days_ago' => 14,
            ],
            [
                'commentaire' => 'Client très respectueux et ponctuel. Tout s\'est très bien passé.',
                'intervention_id' => $intervention1Id,
                'type_auteur' => 'intervenant',
                'days_ago' => 13,
            ],
            [
                'commentaire' => 'Très satisfait du service. Le professionnalisme était au rendez-vous.',
                'intervention_id' => $intervention2Id,
                'type_auteur' => 'client',
                'days_ago' => 9,
            ],
        ];
        
        foreach ($commentaires as $commentaire) {
            $daysAgo = $commentaire['days_ago'];
            unset($commentaire['days_ago']);
            
            if (!DB::table('commentaire')->where('intervention_id', $commentaire['intervention_id'])->where('type_auteur', $commentaire['type_auteur'])->where('commentaire', $commentaire['commentaire'])->exists()) {
                DB::table('commentaire')->insert(array_merge($commentaire, [
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo),
                ]));
            }
        }

        // ============================================
        // 13. Créer des factures
        // ============================================
        if (!DB::table('facture')->where('intervention_id', $intervention1Id)->exists()) {
            DB::table('facture')->insert([
                'fichier_path' => 'factures/facture_' . $intervention1Id . '.pdf',
                'ttc' => $prixMenageResidentiel,
                'intervention_id' => $intervention1Id,
                'created_at' => now()->subDays(14),
                'updated_at' => now()->subDays(14),
            ]);
        }
        
        if (!DB::table('facture')->where('intervention_id', $intervention2Id)->exists()) {
            DB::table('facture')->insert([
                'fichier_path' => 'factures/facture_' . $intervention2Id . '.pdf',
                'ttc' => $prixNettoyageProfondeur,
                'intervention_id' => $intervention2Id,
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(9),
            ]);
        }

        // ============================================
        // 14. Créer des réclamations (pour tester le module réclamations)
        // ============================================
        // Note: Les types doivent être 'Client' et 'Intervenant' (majuscule) selon le morphMap du modèle Reclamation
        $reclamations = [
            [
                'signale_par_id' => $client1UserId,
                'signale_par_type' => 'Client',
                'concernant_id' => $intervenant1UserId,
                'concernant_type' => 'Intervenant',
                'raison' => 'Retard',
                'message' => 'L\'intervenant a eu du retard lors de la dernière intervention.',
                'priorite' => 'moyenne',
                'statut' => 'en_attente',
                'days_ago' => 5,
                'updated_days_ago' => 5,
            ],
            [
                'signale_par_id' => $client2UserId,
                'signale_par_type' => 'Client',
                'concernant_id' => $intervenant1UserId,
                'concernant_type' => 'Intervenant',
                'raison' => 'Qualité du service',
                'message' => 'Le service n\'était pas à la hauteur de mes attentes.',
                'priorite' => 'haute',
                'statut' => 'en_cours',
                'days_ago' => 3,
                'updated_days_ago' => 2,
            ],
            [
                'signale_par_id' => $intervenant1UserId,
                'signale_par_type' => 'Intervenant',
                'concernant_id' => $client3UserId,
                'concernant_type' => 'Client',
                'raison' => 'Comportement inapproprié',
                'message' => 'Le client a été irrespectueux pendant l\'intervention.',
                'priorite' => 'haute',
                'statut' => 'en_attente',
                'days_ago' => 2,
                'updated_days_ago' => 2,
            ],
        ];
        
        foreach ($reclamations as $reclamation) {
            $daysAgo = $reclamation['days_ago'];
            $updatedDaysAgo = $reclamation['updated_days_ago'];
            unset($reclamation['days_ago'], $reclamation['updated_days_ago']);
            
            if (!DB::table('reclamations')
                ->where('signale_par_id', $reclamation['signale_par_id'])
                ->where('signale_par_type', $reclamation['signale_par_type'])
                ->where('concernant_id', $reclamation['concernant_id'])
                ->where('raison', $reclamation['raison'])
                ->exists()) {
                DB::table('reclamations')->insert(array_merge($reclamation, [
                    'notes_admin' => $reclamation['statut'] === 'en_cours' ? 'En cours de traitement par l\'équipe support' : null,
                    'archived' => false,
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($updatedDaysAgo),
                ]));
            }
        }

        // ============================================
        // 15. Créer des favoris (clients qui ont favorisé des intervenants)
        // ============================================
        if (!DB::table('favorise')->where('client_id', $client1UserId)->where('intervenant_id', $intervenant1UserId)->where('service_id', $serviceMenageId)->exists()) {
            DB::table('favorise')->insert([
                'client_id' => $client1UserId,
                'intervenant_id' => $intervenant1UserId,
                'service_id' => $serviceMenageId,
                'created_at' => now()->subDays(16),
                'updated_at' => now()->subDays(16),
            ]);
        }
        
        if (!DB::table('favorise')->where('client_id', $client2UserId)->where('intervenant_id', $intervenant1UserId)->where('service_id', $serviceMenageId)->exists()) {
            DB::table('favorise')->insert([
                'client_id' => $client2UserId,
                'intervenant_id' => $intervenant1UserId,
                'service_id' => $serviceMenageId,
                'created_at' => now()->subDays(11),
                'updated_at' => now()->subDays(11),
            ]);
        }

        // ============================================
        // 16. Créer des disponibilités pour les intervenants
        // ============================================
        $joursSemaine = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        
        foreach ($joursSemaine as $jour) {
            if (!DB::table('disponibilite')
                ->where('intervenant_id', $intervenant1UserId)
                ->where('type', 'reguliere')
                ->where('jours_semaine', $jour)
                ->exists()) {
                DB::table('disponibilite')->insert([
                    'intervenant_id' => $intervenant1UserId,
                    'type' => 'reguliere',
                    'jours_semaine' => $jour,
                    'heure_debut' => '08:00:00',
                    'heure_fin' => '18:00:00',
                    'date_specific' => null,
                ]);
            }
        }

        // Disponibilités pour intervenant 3
        $joursSemaineIntervenant3 = ['lundi', 'mercredi', 'vendredi'];
        foreach ($joursSemaineIntervenant3 as $jour) {
            if (!DB::table('disponibilite')
                ->where('intervenant_id', $intervenant3UserId)
                ->where('type', 'reguliere')
                ->where('jours_semaine', $jour)
                ->exists()) {
                DB::table('disponibilite')->insert([
                    'intervenant_id' => $intervenant3UserId,
                    'type' => 'reguliere',
                    'jours_semaine' => $jour,
                    'heure_debut' => '09:00:00',
                    'heure_fin' => '17:00:00',
                    'date_specific' => null,
                ]);
            }
        }

        // ============================================
        // 17. Créer quelques photos d'interventions (optionnel)
        // ============================================
        $photos = [
            [
                'photo_path' => 'photos/intervention_' . $intervention1Id . '_1.jpg',
                'description' => 'Photo avant nettoyage',
                'phase_prise' => 'avant',
            ],
            [
                'photo_path' => 'photos/intervention_' . $intervention1Id . '_2.jpg',
                'description' => 'Photo après nettoyage',
                'phase_prise' => 'apres',
            ],
        ];
        
        foreach ($photos as $photo) {
            if (!DB::table('photo_intervention')->where('intervention_id', $intervention1Id)->where('photo_path', $photo['photo_path'])->exists()) {
                DB::table('photo_intervention')->insert(array_merge($photo, [
                    'intervention_id' => $intervention1Id,
                    'created_at' => now()->subDays(15),
                    'updated_at' => now()->subDays(15),
                ]));
            }
        }

        // ============================================
        // 18. Résumé des données créées
        // ============================================
        $this->command->info('✅ Données de test créées avec succès !');
        $this->command->info("   - Client 1: tafraoutist@gmail.com (ID: {$client1UserId})");
        $this->command->info("   - Client 2: tafraouti.aya2006@gmail.com (ID: {$client2UserId})");
        $this->command->info("   - Client 3: test.client3@example.com (ID: {$client3UserId})");
        $this->command->info("   - Intervenant 1: tafraouti.sanae@etu.uae.ac.ma (ID: {$intervenant1UserId})");
        $this->command->info("   - Intervenant 2: tafraouti.khaoula@gmail.com (ID: {$intervenant2UserId})");
        $this->command->info("   - Intervenant 3: test.intervenant3@example.com (ID: {$intervenant3UserId})");
        $this->command->info("   - Interventions créées: {$intervention1Id}, {$intervention2Id}, {$intervention3Id}, {$intervention4Id}");
        $this->command->info("   - Admin ID utilisé: {$adminId}");
    }
}
