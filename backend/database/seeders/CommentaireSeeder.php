<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommentaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        
        DB::table('commentaire')->insert([
            // ============================================
            // INTERVENTION 1 - Terminée (Réclamation : Retard)
            // ============================================
            [
                'intervention_id' => 1,
                'commentaire' => 'Excellent travail ! Le jardin est impeccable. Amina a été très professionnelle et soigneuse. Seul bémol : elle est arrivée avec 30 minutes de retard, ce qui m\'a obligé à réorganiser mon planning. Malgré cela, je recommande ses services.',
                'type_auteur' => 'client',
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            [
                'intervention_id' => 1,
                'commentaire' => 'Client très sympathique et accueillant. Le jardin était bien entretenu, ce qui a facilité mon travail. Très bonne communication. Je m\'excuse pour le léger retard dû à un imprévu de circulation.',
                'type_auteur' => 'intervenant',
                'created_at' => $now->copy()->subDays(29),
                'updated_at' => $now->copy()->subDays(29),
            ],
            
            // ============================================
            // INTERVENTION 2 - Terminée (Réclamation : Comportement client - annulations)
            // ============================================
            [
                'intervention_id' => 2,
                'commentaire' => 'Taille de haies parfaitement réalisée. Youssef est un professionnel compétent et ponctuel. Je ferai appel à lui à nouveau.',
                'type_auteur' => 'client',
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            [
                'intervention_id' => 2,
                'commentaire' => 'Intervention réussie et travail effectué dans les délais. La cliente était claire dans ses demandes. Cependant, il y a eu plusieurs annulations et reports au dernier moment avant cette intervention, ce qui a compliqué mon organisation. L\'intervention finale s\'est très bien passée.',
                'type_auteur' => 'intervenant',
                'created_at' => $now->copy()->subDays(24),
                'updated_at' => $now->copy()->subDays(24),
            ],
            
            // ============================================
            // INTERVENTION 3 - Terminée (Réclamation : Matériel manquant)
            // ============================================
            [
                'intervention_id' => 3,
                'commentaire' => 'Nettoyage impeccable ! Sara est méthodique et attentionnée aux détails. La maison est resplendissante. Service de qualité. Petit bémol : elle n\'avait pas tous les produits de nettoyage spécialisés convenus, mais a su s\'adapter avec des alternatives efficaces.',
                'type_auteur' => 'client',
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            [
                'intervention_id' => 3,
                'commentaire' => 'Client très agréable. La maison était déjà bien entretenue, ce qui a rendu le nettoyage plus agréable. Très satisfaite de cette intervention. Je m\'excuse pour l\'oubli de certains produits spécialisés, j\'ai utilisé des alternatives de qualité pour compenser.',
                'type_auteur' => 'intervenant',
                'created_at' => $now->copy()->subDays(19),
                'updated_at' => $now->copy()->subDays(19),
            ],
            
            // ============================================
            // INTERVENTION 4 - Terminée (Réclamation : Qualité du service)
            // ============================================
            [
                'intervention_id' => 4,
                'commentaire' => 'Nettoyage en profondeur correct mais l\'intervenant est arrivé avec 30 minutes de retard. Certaines zones ont été négligées (derrière les meubles, coins difficiles d\'accès). Le travail final est acceptable mais ne correspond pas entièrement à ce qui était promis. La ponctualité et l\'exhaustivité doivent être améliorées.',
                'type_auteur' => 'client',
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            [
                'intervention_id' => 4,
                'commentaire' => 'Intervention correcte. Le client était compréhensif malgré mon léger retard. J\'ai effectué un nettoyage complet et minutieux pour compenser. Je reconnais que certaines zones difficiles d\'accès auraient pu être mieux traitées.',
                'type_auteur' => 'intervenant',
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now->copy()->subDays(14),
            ],
            
            // ============================================
            // INTERVENTION 5 - Terminée (Réclamation : Paiement tardif)
            // ============================================
            [
                'intervention_id' => 5,
                'commentaire' => 'Très beau massif floral créé par Ahmed. Il a su m\'écouter et proposer des variétés adaptées à mon jardin. Résultat au-delà de mes attentes !',
                'type_auteur' => 'client',
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
            [
                'intervention_id' => 5,
                'commentaire' => 'Client très satisfait du travail effectué. Le massif floral a été créé selon ses souhaits. Cependant, le paiement a été retardé de plus de 15 jours malgré plusieurs rappels, ce qui a causé des difficultés de trésorerie. Le travail a été effectué correctement et le client était satisfait.',
                'type_auteur' => 'intervenant',
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(9),
            ],
        ]);
    }
}
