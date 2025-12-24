<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\Intervenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReservationSSEController extends Controller
{
    /**
     * Stream SSE pour les réservations d'un intervenant
     */
    public function stream(Request $request)
    {
        $intervenantId = $request->query('intervenant_id');
        
        // Vérifier l'authentification
        $user = Auth::user();
        if (!$user || (!$user->intervenant && $user->id != $intervenantId)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Headers SSE
        return response()->stream(function() use ($intervenantId) {
            // Envoyer les headers de connexion initiale
            echo "retry: 2000\n";
            echo "event: connected\n";
            echo "data: {\"message\": \"Connecté au flux de réservations\"}\n\n";
            ob_flush();
            flush();

            // Boucle de streaming
            $lastCheck = time();
            $lastInterventionCount = $this->getInterventionCount($intervenantId);
            
            while (true) {
                // Vérifier si le client est toujours connecté
                if (connection_aborted()) {
                    break;
                }

                // Vérifier les nouvelles réservations toutes les 2 secondes
                if (time() - $lastCheck >= 2) {
                    $currentCount = $this->getInterventionCount($intervenantId);
                    
                    // Si nouvelle réservation
                    if ($currentCount > $lastInterventionCount) {
                        $newIntervention = $this->getLatestIntervention($intervenantId);
                        
                        if ($newIntervention) {
                            $this->sendNewReservationEvent($newIntervention);
                        }
                    }
                    
                    $lastCheck = time();
                    $lastInterventionCount = $currentCount;
                }

                // Envoyer un ping pour maintenir la connexion
                echo "event: ping\n";
                echo "data: {\"timestamp\": " . time() . "}\n\n";
                ob_flush();
                flush();

                // Pause pour ne pas surcharger le serveur
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control'
        ]);
    }

    /**
     * Obtenir le nombre d'interventions pour un intervenant
     */
    private function getInterventionCount($intervenantId)
    {
        return Intervention::where('intervenant_id', $intervenantId)->count();
    }

    /**
     * Obtenir la dernière intervention d'un intervenant
     */
    private function getLatestIntervention($intervenantId)
    {
        return Intervention::with(['client.utilisateur', 'tache.service'])
            ->where('intervenant_id', $intervenantId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Envoyer un événement de nouvelle réservation
     */
    private function sendNewReservationEvent($intervention)
    {
        $data = [
            'reservation' => [
                'id' => $intervention->id,
                'service' => $intervention->tache->service->nom_service ?? 'Service Inconnu',
                'task' => $intervention->tache->nom_tache ?? 'Tâche Inconnue',
                'clientName' => $intervention->client->utilisateur 
                    ? $intervention->client->utilisateur->prenom . ' ' . $intervention->client->utilisateur->nom 
                    : 'Client Inconnu',
                'clientImage' => $intervention->client->utilisateur->photo 
                    ? (strpos($intervention->client->utilisateur->photo, 'http') === 0 
                        ? $intervention->client->utilisateur->photo 
                        : url('storage/' . $intervention->client->utilisateur->photo))
                    : 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&h=300&fit=crop',
                'date' => $intervention->date_intervention,
                'location' => $intervention->ville,
                'status' => $intervention->status,
                'created_at' => $intervention->created_at
            ]
        ];

        echo "event: new_reservation\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    /**
     * Envoyer un événement de mise à jour de statut
     */
    public static function sendStatusUpdate($interventionId, $newStatus)
    {
        // Cette méthode peut être appelée depuis d'autres contrôleurs
        // pour notifier les changements de statut
        
        $data = [
            'reservation_id' => $interventionId,
            'updates' => [
                'status' => $newStatus,
                'updated_at' => now()->toISOString()
            ]
        ];

        // Note: Pour une implémentation complète, il faudrait stocker
        // les connexions SSE actives et broadcaster à toutes les connexions
        // concernées. Pour l'instant, cette méthode est un placeholder
        // pour l'architecture future.
    }
}
