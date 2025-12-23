<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Evaluation;
use App\Models\Facture;
use App\Models\Favorise;
use App\Models\Critaire;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientProfileController extends Controller
{
    /**
     * Get client profile statistics
     */
    public function getStatistics($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            // Get completed interventions count - use flexible statuses
            $completedInterventions = Intervention::where('client_id', $clientId)
                ->whereIn('status', ['terminée', 'terminee', 'terminées', 'terminees', 'termine'])
                ->count();

            // Get average rating from intervenants (evaluations where type_auteur = 'intervenant')
            $intervenantEvaluations = Evaluation::whereHas('intervention', function($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->where('type_auteur', 'intervenant')->get();

            $averageRating = 0;
            if ($intervenantEvaluations->count() > 0) {
                $averageRating = round($intervenantEvaluations->avg('note'), 1);
            }

            // Get total DH (total from all factures)
            $totalDH = Facture::whereHas('intervention', function($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->sum('ttc') ?? 0;

            // Get favorites count
            $favoritesCount = DB::table('favorise')->where('client_id', $clientId)->count();

            return response()->json([
                'averageRating' => $averageRating,
                'servicesCount' => $completedInterventions,
                'totalDH' => round($totalDH),
                'favoritesCount' => $favoritesCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Get client profile history (completed interventions)
     */
    public function getHistory($clientId)
    {
        try {
            $interventions = Intervention::where('client_id', $clientId)
                ->whereIn('status', ['terminée', 'terminee', 'terminées', 'terminees', 'termine', 'completed'])
                ->with([
                    'intervenant.utilisateur',
                    'tache.service',
                    'facture',
                    'evaluations' => function($query) {
                        $query->where('type_auteur', 'client');
                    },
                    'commentaires' => function($query) {
                        $query->where('type_auteur', 'client');
                    }
                ])
                ->orderBy('date_intervention', 'desc')
                ->get();

            $history = $interventions->map(function($intervention) {
                // Calculate client's average rating for this intervention
                $clientEvaluations = $intervention->evaluations;
                $overallRating = null;
                
                if ($clientEvaluations->count() > 0) {
                    $overallRating = round($clientEvaluations->avg('note'), 1);
                }

                // Get client's comment
                $comment = $intervention->commentaires->first()->commentaire ?? null;

                // Get service name
                $serviceName = $intervention->tache->service->nom_service ?? 'Service';
                $taskName = $intervention->tache->nom_tache ?? '';
                $fullServiceName = $serviceName . ($taskName ? ' - ' . $taskName : '');

                return [
                    'id' => $intervention->id,
                    'serviceName' => $fullServiceName,
                    'providerId' => $intervention->intervenant_id,
                    'providerName' => trim(($intervention->intervenant->utilisateur->prenom ?? '') . ' ' . ($intervention->intervenant->utilisateur->nom ?? '')),
                    'providerImage' => $intervention->intervenant->utilisateur->url ?? null,
                    'date' => $intervention->date_intervention ? $intervention->date_intervention->format('d/m/Y') : 'N/A',
                    'price' => $intervention->facture->ttc ?? 0,
                    'rating' => $overallRating,
                    'comment' => $comment
                ];
            });

            return response()->json([
                'data' => $history,
                'count' => $history->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Get client evaluations (reviews received from intervenants)
     */
    public function getEvaluations($clientId)
    {
        try {
            // Get all evaluations from intervenants for this client's interventions
            $evaluations = Evaluation::whereHas('intervention', function($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->where('type_auteur', 'intervenant')
            ->with([
                'intervention.intervenant.utilisateur',
                'intervention.tache.service',
                'critaire',
                'intervention.commentaires' => function($query) {
                    $query->where('type_auteur', 'intervenant');
                }
            ])
            ->get();

            // Group evaluations by intervention
            $interventionsMap = [];
            foreach ($evaluations as $evaluation) {
                $interventionId = $evaluation->intervention_id;
                
                if (!isset($interventionsMap[$interventionId])) {
                    $intervention = $evaluation->intervention;
                    $interventionsMap[$interventionId] = [
                        'interventionId' => $interventionId,
                        'intervenantName' => trim(($intervention->intervenant->utilisateur->prenom ?? '') . ' ' . ($intervention->intervenant->utilisateur->nom ?? '')),
                        'intervenantImage' => $intervention->intervenant->utilisateur->url ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
                        'serviceName' => ($intervention->tache->service->nom_service ?? '') . ' - ' . ($intervention->tache->nom_tache ?? ''),
                        'date' => $this->formatFrenchDate($intervention->date_intervention),
                        'criteriaRatings' => [],
                        'overallRating' => null,
                        'comment' => null,
                        'recommends' => true // Default to true
                    ];
                }

                // Add criteria rating
                $criteriaName = strtolower(str_replace(' ', '_', $evaluation->critaire->nom_critaire ?? ''));
                $interventionsMap[$interventionId]['criteriaRatings'][$criteriaName] = $evaluation->note;
            }

            // Calculate overall ratings and get comments
            $evaluationsList = [];
            foreach ($interventionsMap as $interventionId => $data) {
                $criteriaRatings = array_values($data['criteriaRatings']);
                if (count($criteriaRatings) > 0) {
                    $data['overallRating'] = round(array_sum($criteriaRatings) / count($criteriaRatings), 1);
                }

                // Get comment from intervenant
                $intervention = Intervention::find($interventionId);
                if ($intervention) {
                    $comment = $intervention->commentaires->where('type_auteur', 'intervenant')->first();
                    if ($comment) {
                        $data['comment'] = $comment->commentaire;
                    }
                }

                $evaluationsList[] = $data;
            }

            // Sort by date descending
            usort($evaluationsList, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Calculate overall statistics
            $allRatings = [];
            $criteriaStats = [];
            
            foreach ($evaluationsList as $eval) {
                if ($eval['overallRating']) {
                    $allRatings[] = $eval['overallRating'];
                }
                foreach ($eval['criteriaRatings'] as $criteria => $rating) {
                    if (!isset($criteriaStats[$criteria])) {
                        $criteriaStats[$criteria] = [];
                    }
                    $criteriaStats[$criteria][] = $rating;
                }
            }

            $averageRating = count($allRatings) > 0 ? round(array_sum($allRatings) / count($allRatings), 1) : null;
            $satisfactionRate = count($evaluationsList) > 0 ? 100 : 0; // Assuming all recommend if they left a review
            $clientStatus = 'Excellent'; // Can be calculated based on ratings

            $criteriaAverages = [];
            foreach ($criteriaStats as $criteria => $ratings) {
                $criteriaAverages[$criteria] = round(array_sum($ratings) / count($ratings), 2);
            }

            return response()->json([
                'data' => $evaluationsList,
                'statistics' => [
                    'averageRating' => $averageRating,
                    'satisfactionRate' => $satisfactionRate,
                    'clientStatus' => $clientStatus,
                    'totalEvaluations' => count($evaluationsList),
                    'criteriaAverages' => $criteriaAverages
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des évaluations',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Format date in French format
     */
    private function formatFrenchDate($date)
    {
        $months = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];
        
        $day = $date->format('d');
        $month = $months[(int)$date->format('n')];
        $year = $date->format('Y');
        
        return "{$day} {$month} {$year}";
    }
}

