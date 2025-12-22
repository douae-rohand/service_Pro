<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get statistics for the homepage
     * Returns:
     * - satisfied_clients: Number of active clients (or all clients)
     * - qualified_intervenants: Number of active intervenants (or all intervenants)
     * - completed_services: Number of completed interventions (or all interventions)
     */
    public function index()
    {
        try {
            // Compter tous les clients
            $allClients = Client::count();

            // Compter tous les intervenants
            $allIntervenants = Intervenant::count();

            // Compter tous les sous-services (Taches)
            // Note: On suppose que "sous services" correspond au modèle Tache
            $allSubServices = \App\Models\Tache::count();

            $stats = [
                'satisfied_clients' => (int) $allClients,
                'qualified_intervenants' => (int) $allIntervenants,
                'completed_services' => (int) $allSubServices, // Keeping key for compatibility, but value is Taches
                'clients_count' => (int) $allClients,
                'intervenants_count' => (int) $allIntervenants,
                'subservices_count' => (int) $allSubServices
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des valeurs par défaut
            \Log::error('Erreur dans StatsController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'satisfied_clients' => 0,
                'qualified_intervenants' => 0,
                'completed_services' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews and statistics for a specific intervenant
     */
    public function getIntervenantReviewsStats($intervenantId)
    {
        try {
            // Get all interventions for this intervenant that are completed
            $interventions = Intervention::where('intervenant_id', $intervenantId)
                ->where('status', 'termine')
                ->with(['evaluations', 'commentaires', 'client.utilisateur', 'fichePayement', 'tache.service'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Filter interventions to only include those with public ratings
            $publicInterventions = $interventions->filter(function ($intervention) {
                return $intervention->areRatingsPublic();
            });

            // Calculate statistics
            $totalReviews = 0;
            $totalRating = 0;
            $ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $reviews = [];

            foreach ($publicInterventions as $intervention) {
                // Get evaluations for this intervention
                $evaluations = $intervention->evaluations->where('type_auteur', 'client');

                // Calculate average rating for this intervention
                $interventionAvgRating = 0;
                $evaluationCount = $evaluations->count();

                if ($evaluationCount > 0) {
                    $totalRatingSum = $evaluations->sum('note');
                    $interventionAvgRating = round($totalRatingSum / $evaluationCount, 1);

                    // Update global statistics
                    $totalReviews++;
                    $totalRating += $interventionAvgRating;

                    // Round the average rating for distribution
                    $roundedRating = round($interventionAvgRating);
                    if ($roundedRating >= 1 && $roundedRating <= 5) {
                        $ratings[$roundedRating]++;
                    }

                    // Get corresponding comment if exists
                    $comment = $intervention->commentaires
                        ->where('type_auteur', 'client')
                        ->first();

                    $client = $intervention->client;
                    $user = $client ? $client->utilisateur : null;

                    // Build details array
                    $clientEvaluations = $evaluations; // Re-use the filtered validations
                    $criteriaDetails = [];

                    foreach ($clientEvaluations as $eval) {
                        $criteriaDetails[] = [
                            'criteriaName' => $eval->critaire ? $eval->critaire->nom_critaire : 'Critère',
                            'rating' => $eval->note
                        ];
                    }

                    $reviews[] = [
                        'id' => $intervention->id,
                        'intervention_id' => $intervention->id, // Explicit ID for complaints
                        'clientName' => $user ? ($user->prenom . ' ' . $user->nom) : 'Client Anonyme',
                        'clientImage' => $user && $user->profile_photo ? url('storage/' . $user->profile_photo) : null,
                        'rating' => $interventionAvgRating,
                        'date' => $intervention->updated_at,
                        'service' => $intervention->tache ? $intervention->tache->nom_tache : 'Service',
                        'mainService' => $intervention->tache && $intervention->tache->service ? $intervention->tache->service->nom_service : 'Service',
                        'location' => $intervention->address ? ($intervention->address . ($intervention->ville ? ', ' . $intervention->ville : '')) : 'Lieu non spécifié',
                        'comment' => $comment ? $comment->commentaire : '',
                        'details' => $criteriaDetails
                    ];
                }
            }

            // Calculate average rating
            $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;

            // Calculate response rate (intervenant comments / total client comments) - only for public interventions
            $clientComments = $publicInterventions->sum(function ($intervention) {
                return $intervention->commentaires->where('type_auteur', 'client')->count();
            });

            $intervenantComments = $publicInterventions->sum(function ($intervention) {
                return $intervention->commentaires->where('type_auteur', 'intervenant')->count();
            });

            $responseRate = $clientComments > 0 ? round(($intervenantComments / $clientComments) * 100) : 0;

            // Calculate satisfaction rate (4+ stars ratings)
            $satisfiedRatings = $ratings[4] + $ratings[5];
            $satisfactionRate = $totalReviews > 0 ? round(($satisfiedRatings / $totalReviews) * 100) : 0;

            // Sort reviews by date (newest first)
            usort($reviews, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Calculate completed missions count (ALL completed interventions)
            $completedMissions = $interventions->count();

            // Calculate total revenue from ALL completed interventions
            $totalAmount = 0;
            foreach ($interventions as $intervention) {
                if ($intervention->fichePayement && $intervention->fichePayement->ttc !== null) {
                    $totalAmount += (float) $intervention->fichePayement->ttc;
                }
            }

            return response()->json([
                'stats' => [
                    'averageRating' => $averageRating,
                    'totalReviews' => $totalReviews,
                    'completedMissions' => $completedMissions,
                    'totalAmount' => $totalAmount,
                    'responseRate' => $responseRate,
                    'satisfactionRate' => $satisfactionRate,
                    'distribution' => [
                        ['stars' => 5, 'count' => $ratings[5]],
                        ['stars' => 4, 'count' => $ratings[4]],
                        ['stars' => 3, 'count' => $ratings[3]],
                        ['stars' => 2, 'count' => $ratings[2]],
                        ['stars' => 1, 'count' => $ratings[1]],
                    ]
                ],
                'reviews' => array_slice($reviews, 0, 10) // Return latest 10 reviews
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans StatsController::getIntervenantReviewsStats: ' . $e->getMessage(), [
                'intervenantId' => $intervenantId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'stats' => [
                    'averageRating' => 0,
                    'totalReviews' => 0,
                    'completedMissions' => 0,
                    'totalAmount' => 0,
                    'responseRate' => 0,
                    'satisfactionRate' => 0,
                    'distribution' => [
                        ['stars' => 5, 'count' => 0],
                        ['stars' => 4, 'count' => 0],
                        ['stars' => 3, 'count' => 0],
                        ['stars' => 2, 'count' => 0],
                        ['stars' => 1, 'count' => 0],
                    ]
                ],
                'reviews' => []
            ], 500);
        }
    }
}

