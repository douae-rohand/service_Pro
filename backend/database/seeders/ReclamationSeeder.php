<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReclamationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crée des réclamations liées aux interventions existantes.
     * Les réclamations doivent avoir un intervention_id pour suivre la logique recommandée.
     */
    public function run(): void
    {
        $now = now();
        
        // Récupérer les interventions terminées pour créer des réclamations réalistes
        $interventions = DB::table('intervention')
            ->where('status', 'termine')
            ->limit(5)
            ->get();
        
        if ($interventions->isEmpty()) {
            $this->command->warn('⚠️  Aucune intervention terminée trouvée. Les réclamations nécessitent des interventions terminées.');
            return;
        }
        
        $reclamations = [];
        
        // Réclamation 1 : Client réclame pour retard (intervention 1)
        // Cohérent : Même avec un excellent travail final (notes 5/5), un retard peut être réclamé
        if ($interventions->count() > 0) {
            $interv1 = $interventions[0];
            $reclamations[] = [
                'signale_par_id' => $interv1->client_id,
                'signale_par_type' => 'Client',
                'concernant_id' => $interv1->intervenant_id,
                'concernant_type' => 'Intervenant',
                'intervention_id' => $interv1->id,
                'raison' => 'Retard',
                'message' => 'L\'intervenant est arrivé avec 30 minutes de retard. Bien que le travail ait été excellent et que je sois satisfait du résultat final, la ponctualité est importante pour moi et j\'ai dû réorganiser mon planning.',
                'priorite' => 'moyenne',
                'statut' => 'en_attente',
                'notes_admin' => null,
                'archived' => false,
                'days_ago' => 5,
                'updated_days_ago' => 5,
            ];
        }
        
        // Réclamation 2 : Client réclame pour qualité du service (intervention 4)
        // Cohérent : Notes basses (3-4/5) et commentaire mentionne un retard et qualité moyenne
        if ($interventions->count() > 3) {
            $interv4 = $interventions[3];
            $reclamations[] = [
                'signale_par_id' => $interv4->client_id,
                'signale_par_type' => 'Client',
                'concernant_id' => $interv4->intervenant_id,
                'concernant_type' => 'Intervenant',
                'intervention_id' => $interv4->id,
                'raison' => 'Qualité du service',
                'message' => 'Le nettoyage en profondeur n\'était pas complet. Certaines zones ont été négligées (derrière les meubles, coins difficiles d\'accès). De plus, l\'intervenant est arrivé en retard. Le travail final est acceptable mais ne correspond pas à ce qui était promis.',
                'priorite' => 'haute',
                'statut' => 'en_cours',
                'notes_admin' => 'En cours de traitement par l\'équipe support. Contact avec les deux parties en cours. L\'intervenant reconnaît le retard et s\'engage à améliorer.',
                'archived' => false,
                'days_ago' => 12,
                'updated_days_ago' => 2,
            ];
        }
        
        // Réclamation 3 : Intervenant réclame contre client (intervention 2)
        // Changé : Au lieu d'un commentaire négatif (qui n'existe pas), le client a annulé plusieurs fois avant
        if ($interventions->count() > 1) {
            $interv2 = $interventions[1];
            $reclamations[] = [
                'signale_par_id' => $interv2->intervenant_id,
                'signale_par_type' => 'Intervenant',
                'concernant_id' => $interv2->client_id,
                'concernant_type' => 'Client',
                'intervention_id' => $interv2->id,
                'raison' => 'Comportement client',
                'message' => 'Le client a annulé et reporté l\'intervention à plusieurs reprises au dernier moment, ce qui m\'a causé des pertes de temps et d\'organisation. Bien que l\'intervention finale se soit bien passée, ce comportement est problématique pour mon planning.',
                'priorite' => 'moyenne',
                'statut' => 'en_attente',
                'notes_admin' => null,
                'archived' => false,
                'days_ago' => 8,
                'updated_days_ago' => 8,
            ];
        }
        
        // Réclamation 4 : Client réclame pour matériel manquant (intervention 3) - Résolue
        // Cohérent : Même avec un bon travail, un problème de matériel peut survenir
        if ($interventions->count() > 2) {
            $interv3 = $interventions[2];
            $reclamations[] = [
                'signale_par_id' => $interv3->client_id,
                'signale_par_type' => 'Client',
                'concernant_id' => $interv3->intervenant_id,
                'concernant_type' => 'Intervenant',
                'intervention_id' => $interv3->id,
                'raison' => 'Matériel manquant',
                'message' => 'L\'intervenant n\'avait pas tout le matériel nécessaire (certains produits de nettoyage spécialisés). Elle a dû utiliser des alternatives qui, bien qu\'efficaces, n\'étaient pas celles convenues initialement. Le travail final reste de qualité mais cela a causé un léger retard.',
                'priorite' => 'basse',
                'statut' => 'resolu',
                'notes_admin' => 'Problème résolu : l\'intervenant s\'est engagé à mieux préparer son matériel à l\'avenir. Le client est satisfait de la résolution.',
                'archived' => false,
                'days_ago' => 20,
                'updated_days_ago' => 15,
            ];
        }
        
        // Réclamation 5 : Intervenant réclame pour paiement tardif (intervention 5) - Archivée
        // Nouvelle réclamation réaliste
        if ($interventions->count() > 4) {
            $interv5 = $interventions[4];
            $reclamations[] = [
                'signale_par_id' => $interv5->intervenant_id,
                'signale_par_type' => 'Intervenant',
                'concernant_id' => $interv5->client_id,
                'concernant_type' => 'Client',
                'intervention_id' => $interv5->id,
                'raison' => 'Paiement',
                'message' => 'Le client n\'a pas effectué le paiement dans les délais convenus malgré plusieurs rappels. Le travail a été effectué correctement et le client était satisfait, mais le paiement a été retardé de plus de 15 jours.',
                'priorite' => 'haute',
                'statut' => 'resolu',
                'notes_admin' => 'Résolu : le paiement a été effectué après médiation. Le client s\'est excusé pour le retard et s\'engage à respecter les délais à l\'avenir.',
                'archived' => true,
                'days_ago' => 30,
                'updated_days_ago' => 25,
            ];
        }
        
        // Insérer les réclamations
        foreach ($reclamations as $reclamation) {
            $daysAgo = $reclamation['days_ago'];
            $updatedDaysAgo = $reclamation['updated_days_ago'];
            unset($reclamation['days_ago'], $reclamation['updated_days_ago']);
            
            // Vérifier si la réclamation existe déjà
            if (!DB::table('reclamations')
                ->where('intervention_id', $reclamation['intervention_id'])
                ->where('signale_par_id', $reclamation['signale_par_id'])
                ->where('signale_par_type', $reclamation['signale_par_type'])
                ->where('raison', $reclamation['raison'])
                ->exists()) {
                DB::table('reclamations')->insert(array_merge($reclamation, [
                    'created_at' => $now->copy()->subDays($daysAgo),
                    'updated_at' => $now->copy()->subDays($updatedDaysAgo),
                ]));
            }
        }
        
        $this->command->info('✅ Réclamations créées avec succès !');
    }
}

