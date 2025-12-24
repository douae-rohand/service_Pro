<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use App\Models\Reclamation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSSEController extends Controller
{
    /**
     * Stream SSE pour les mises à jour admin (Global stats)
     */
    public function stream(Request $request)
    {
        // Désactiver la limite de temps d'exécution
        set_time_limit(0);
        
        // Headers SSE
        $response = new StreamedResponse(function () {
            // État initial
            $lastHash = null;
            
            // Boucle infinie
            while (true) {
                if (connection_aborted()) {
                    break;
                }

                // 1. Collecter les statistiques clés
                $stats = $this->collectStats();
                
                // 2. Générer un hash pour détecter les changements
                $currentHash = md5(json_encode($stats));
                
                // 3. Si changement, envoyer l'événement
                if ($currentHash !== $lastHash) {
                    $this->sendEvent('stats_update', $stats);
                    $lastHash = $currentHash;
                }
                
                // 4. Ping pour maintenir la connexion (toutes les 10 secondes approx, géré par le client ou ici)
                // On peut envoyer un ping léger
                 echo "event: ping\n";
                 echo "data: " . time() . "\n\n";
                 
                 ob_flush();
                 flush();

                // Pause de 3 secondes
                sleep(3);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Pour Nginx

        return $response;
    }

    private function collectStats()
    {
        // Optimiser les requêtes pour qu'elles soient légères (Count Only)
        
        // 1. Clients Total
        $totalClients = Client::count();
        
        // 2. Intervenants Actifs
        $totalIntervenants = Intervenant::where('is_active', true)->count();
        
        // 3. Interventions Ce mois
        $now = now();
        $interventionsMois = Intervention::whereMonth('date_intervention', $now->month)
            ->whereYear('date_intervention', $now->year)
            ->count();
            
        // 4. Demandes en attente (Logique complexe copiée de AdminController)
        $demandesEnAttente = Intervenant::where('is_active', true)
            ->whereHas('services', function($q) {
                $q->where('intervenant_service.status', 'demmande');
                if (Schema::hasColumn('service', 'status')) {
                    $q->where('service.status', 'active');
                }
            })
            ->count();
            // Note: Le count ci-dessus compte les INTERVENANTS qui ont des demandes, pas les demandes elles-mêmes.
            // Si un intervenant a 2 demandes, c'est mieux de compter les demandes.
            // Correction pour compter les demandes exactes :
            $demandesCount = \DB::table('intervenant_service')
                ->join('intervenant', 'intervenant_service.intervenant_id', '=', 'intervenant.id')
                ->join('service', 'intervenant_service.service_id', '=', 'service.id')
                ->where('intervenant.is_active', true)
                ->where('intervenant_service.status', 'demmande')
                ->where(function($q) {
                    // Si la colonne existe
                     $q->where('service.status', 'active'); // On assume active par défaut si la colonne n'existe pas dans la migration mais la logique PHP check Schema
                })
                ->count();
                
            // Utilisons une approche plus sûre compatible avec le code existant qui check le schema
            // Comme c'est du raw DB query, on va simplifier en assumant que service.status existe car c'est utilisé partout
            // Si elle n'existe pas, la condition plantera, donc checkons d'abord si on peut faire une requête Eloquent propre
            // Mais pour la performance, count direct sur le pivot est mieux.
            // On va stick à la logique "Intervenants avec demandes" pour l'instant si c'est ce que le badge affiche,
            // ou mieux : le nombre de demandes total.
            
            // Version Count Eloquent précise :
            $demandesEnAttente = Intervenant::with(['services' => function($q) {
                 $q->wherePivot('status', 'demmande');
            }])
            ->where('is_active', true)
            ->get()
            ->sum(function($intervenant) {
                return $intervenant->services->filter(function($service) {
                     return $service->pivot->status === 'demmande' 
                        && ($service->status === 'active' || !isset($service->status));
                })->count();
            });


        // 5. Nouvelles Réclamations
        $reclamationsNouvelles = Reclamation::where('statut', 'nouveau')->count();

        // 6. Satisfaction (Optionnel car lourd, peut-être ne pas l'inclure dans le stream rapide ou le cacher 5 min)
        // On peut l'exclure du check de changement "fréquent" et laisser le frontend le charger via API standard
        // Mais pour l'instant on renvoie les compteurs critiques.

        return [
            'totalClients' => $totalClients,
            'totalIntervenants' => $totalIntervenants,
            'interventionsMois' => $interventionsMois,
            'demandesEnAttente' => $demandesEnAttente,
            'reclamationsNouvelles' => $reclamationsNouvelles,
            'timestamp' => time()
        ];
    }

    private function sendEvent($event, $data)
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }
}
