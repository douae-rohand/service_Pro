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
            // Compter les clients (Clients Satisfaits)
            $satisfiedClients = Client::where('is_active', true)->count();
            if ($satisfiedClients === 0) {
                $satisfiedClients = Client::count();
            }

            // Compter les intervenants (Intervenants Qualifiés)
            $qualifiedIntervenants = Intervenant::where('is_active', true)->count();
            if ($qualifiedIntervenants === 0) {
                $qualifiedIntervenants = Intervenant::count();
            }

            // Compter les interventions terminées (Services Complétés)
            $completedServices = Intervention::where('status', 'terminée')->count();
            if ($completedServices === 0) {
                // Si aucune terminée, compter toutes pour la démo
                $completedServices = Intervention::count();
            }

            $stats = [
                'satisfied_clients' => (int) $satisfiedClients,
                'qualified_intervenants' => (int) $qualifiedIntervenants,
                'completed_services' => (int) $completedServices,
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
                ->with(['evaluations', 'commentaires', 'client.utilisateur', 'facture', 'tache.service'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Calculate statistics
            $totalReviews = 0;
            $totalRating = 0;
            $ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $reviews = [];

            foreach ($interventions as $intervention) {
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
                    $details = $evaluations->map(function ($eval) {
                        return [
                            'criteria' => $eval->critaire ? $eval->critaire->nom_critaire : 'Critère',
                            'rating' => $eval->note
                        ];
                    })->values();

                    $reviews[] = [
                        'id' => $intervention->id, // Use intervention ID to avoid duplicates
                        'clientName' => $user ? ($user->prenom . ' ' . $user->nom) : 'Client Anonyme',
                        'clientImage' => null, // TODO: Add client image if available
                        'rating' => $interventionAvgRating,
                        'date' => $intervention->updated_at, // Use intervention date
                        'service' => $intervention->tache ? $intervention->tache->nom_tache : 'Service',
                        'mainService' => $intervention->tache && $intervention->tache->service ? $intervention->tache->service->nom_service : 'Service',
                        'location' => $intervention->address ? ($intervention->address . ($intervention->ville ? ', ' . $intervention->ville : '')) : 'Lieu non spécifié',
                        'comment' => $comment ? $comment->commentaire : '',
                        'details' => $details
                    ];
                }
            }

            // Calculate average rating
            $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;

            // Calculate response rate (intervenant comments / total client comments)
            $clientComments = $interventions->sum(function ($intervention) {
                return $intervention->commentaires->where('type_auteur', 'client')->count();
            });

            $intervenantComments = $interventions->sum(function ($intervention) {
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

            // Calculate completed missions count
            $completedMissions = $interventions->count();

            // Calculate total revenue from completed interventions
            $totalAmount = 0;
            foreach ($interventions as $intervention) {
                if ($intervention->facture && $intervention->facture->ttc !== null) {
                    $totalAmount += (float) $intervention->facture->ttc;
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

