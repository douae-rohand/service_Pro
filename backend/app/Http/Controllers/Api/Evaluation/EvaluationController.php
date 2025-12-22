<?php

namespace App\Http\Controllers\Api\Evaluation;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Intervention;
use App\Models\Commentaire;
use App\Models\Critaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class EvaluationController extends Controller
{
    /**
     * Submit evaluation for an intervention
     */
    public function store(Request $request, $interventionId)
    {
        $intervention = Intervention::findOrFail($interventionId);

        // Check if intervention is completed
        $status = strtolower($intervention->status ?? '');
        if (!in_array($status, ['terminee', 'terminée', 'terminées'])) {
            return response()->json([
                'message' => 'Vous ne pouvez évaluer que les interventions terminées',
                'error' => 'invalid_status'
            ], 400);
        }

        // Check if already evaluated
        $existingEvaluations = Evaluation::where('intervention_id', $interventionId)
            ->where('type_auteur', 'client')
            ->count();

        if ($existingEvaluations > 0) {
            return response()->json([
                'message' => 'Cette intervention a déjà été évaluée',
                'error' => 'already_evaluated'
            ], 400);
        }

        // Check if within 7 days of completion
        $completedDate = Carbon::parse($intervention->updated_at);
        $daysSinceCompletion = Carbon::now()->diffInDays($completedDate);
        
        if ($daysSinceCompletion > 7) {
            return response()->json([
                'message' => 'Le délai de 7 jours pour évaluer cette prestation est dépassé',
                'error' => 'evaluation_expired'
            ], 400);
        }

        $validated = $request->validate([
            'criteriaRatings' => 'required|array',
            'criteriaRatings.*' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'wouldRecommend' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Check if intervenant has already rated this intervention
            $intervenantHasRated = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'intervenant')
                ->exists();
            
            // If both parties have now rated, set is_public to true
            $isPublic = $intervenantHasRated;
            
            // Get all criteria for mapping if needed
            $criteria = Critaire::where('type', 'intervenant')->get();
            $criteriaMap = [];
            foreach ($criteria as $c) {
                $key = strtolower(str_replace(' ', '_', $c->nom_critaire ?? ''));
                // Map French criteria names to English keys
                $criteriaNameMap = [
                    'qualité_du_travail' => 'quality',
                    'ponctualité' => 'punctuality',
                    'professionnalisme' => 'professionalism',
                    'communication' => 'communication',
                    'propreté' => 'cleanup',
                    'rapport_qualité/prix' => 'value',
                ];
                $mappedKey = $criteriaNameMap[$key] ?? $key;
                $criteriaMap[$mappedKey] = $c->id;
            }

            // Create evaluations for each criteria
            foreach ($validated['criteriaRatings'] as $criteriaKey => $note) {
                $critaireId = null;
                
                // Check if the key is numeric (new format with IDs)
                if (is_numeric($criteriaKey)) {
                    $critaireId = (int)$criteriaKey;
                } else {
                    // Old format with string keys (quality, punctuality, etc.)
                    $critaireId = $criteriaMap[$criteriaKey] ?? null;
                }
                
                if ($critaireId) {
                    Evaluation::create([
                        'intervention_id' => $interventionId,
                        'critaire_id' => $critaireId,
                        'note' => $note,
                        'type_auteur' => 'client',
                        'is_public' => $isPublic,
                    ]);
                }
            }

            // If both parties have rated, update intervenant's evaluations to public
            if ($isPublic) {
                Evaluation::where('intervention_id', $interventionId)
                    ->where('type_auteur', 'intervenant')
                    ->update(['is_public' => true]);
                    
                // Also update comments to public
                Commentaire::where('intervention_id', $interventionId)
                    ->where('type_auteur', 'intervenant')
                    ->update(['is_public' => true]);
            }

            // Create comment if provided
            if (!empty($validated['comment'])) {
                Commentaire::create([
                    'intervention_id' => $interventionId,
                    'commentaire' => $validated['comment'],
                    'type_auteur' => 'client',
                    'is_public' => $isPublic,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Évaluation soumise avec succès',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing evaluation:', [
                'intervention_id' => $interventionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la soumission de l\'évaluation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluation for an intervention
     */
    public function show($interventionId)
    {
        $intervention = Intervention::with([
            'evaluations.critaire',
            'commentaires'
        ])->findOrFail($interventionId);

        $clientEvaluations = $intervention->evaluations->where('type_auteur', 'client');
        
        if ($clientEvaluations->count() === 0) {
            return response()->json([
                'message' => 'Aucune évaluation trouvée',
                'evaluation' => null
            ]);
        }

        $criteriaRatings = [];
        $totalRating = 0;
        
        foreach ($clientEvaluations as $evaluation) {
            $criteriaName = strtolower(str_replace(' ', '_', $evaluation->critaire->nom_critaire ?? 'quality'));
            $criteriaRatings[$criteriaName] = $evaluation->note ?? 0;
            $totalRating += $evaluation->note ?? 0;
        }
        
        $commentaire = $intervention->commentaires->where('type_auteur', 'client')->first();
        
        return response()->json([
            'evaluation' => [
                'criteriaRatings' => $criteriaRatings,
                'overallRating' => round($totalRating / $clientEvaluations->count(), 1),
                'comment' => $commentaire->commentaire ?? '',
                'wouldRecommend' => true
            ]
        ]);
    }

    /**
     * Get client evaluation criteria for intervenant rating.
     */
    public function getClientCriteria(): JsonResponse
    {
        $criteria = Critaire::where('type', 'client')->get();

        return response()->json($criteria);
    }

    /**
     * Get intervenant evaluation criteria for client rating.
     */
    public function getIntervenantCriteria(): JsonResponse
    {
        $criteria = Critaire::where('type', 'intervenant')->get();

        return response()->json($criteria);
    }

    /**
     * Store client evaluation by intervenant.
     */
    public function storeClientEvaluation(Request $request, int $interventionId): JsonResponse
    {
        try {
            $request->validate([
                'evaluations' => 'required|array|min:1',
                'evaluations.*.critaire_id' => 'required|integer|exists:critaire,id',
                'evaluations.*.note' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500'
            ]);

            $intervention = Intervention::findOrFail($interventionId);

            // Check if intervention is finished
            if ($intervention->status !== 'termine') {
                return response()->json([
                    'message' => 'Vous ne pouvez évaluer que les interventions terminées'
                ], 403);
            }

            // Check if within 7-day voting window
            $interventionDate = $intervention->updated_at;
            $sevenDaysAgo = now()->subDays(7);
            if (!$interventionDate->greaterThan($sevenDaysAgo)) {
                return response()->json([
                    'message' => 'Délai de 7 jours dépassé - Impossible d\'évaluer'
                ], 403);
            }

            // Check if current user is the intervenant of this intervention
            $currentUser = Auth::user();
            $intervenant = $currentUser->intervenant;
            if (!$intervenant || $intervenant->id !== $intervention->intervenant_id) {
                return response()->json([
                    'message' => 'Seul l\'intervenant assigné peut évaluer le client'
                ], 403);
            }

            // Check if already rated (prevent re-rating after initial submission)
            $existingRatings = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'intervenant')
                ->count();

            if ($existingRatings > 0) {
                return response()->json([
                    'message' => 'Vous avez déjà évalué cette intervention'
                ], 403);
            }

            // Check if client has already rated this intervention
            $clientHasRated = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'client')
                ->exists();
            
            // If both parties have now rated, set is_public to true
            $isPublic = $clientHasRated;
            
            // Store evaluations
            foreach ($request->evaluations as $evaluation) {
                Evaluation::create([
                    'intervention_id' => $interventionId,
                    'critaire_id' => $evaluation['critaire_id'],
                    'note' => $evaluation['note'],
                    'type_auteur' => 'intervenant',
                    'is_public' => $isPublic,
                ]);
            }

            // If both parties have rated, update client's evaluations to public
            if ($isPublic) {
                Evaluation::where('intervention_id', $interventionId)
                    ->where('type_auteur', 'client')
                    ->update(['is_public' => true]);
                    
                // Also update comments to public
                Commentaire::where('intervention_id', $interventionId)
                    ->where('type_auteur', 'client')
                    ->update(['is_public' => true]);
            }

            // Store comment if provided
            if ($request->has('comment') && !empty($request->comment)) {
                Commentaire::create([
                    'intervention_id' => $interventionId,
                    'commentaire' => $request->comment,
                    'type_auteur' => 'intervenant',
                    'is_public' => $isPublic,
                ]);
            }

            return response()->json([
                'message' => 'Évaluation du client enregistrée avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement de l\'évaluation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client evaluations for a specific intervention.
     */
    public function getClientEvaluations(int $interventionId): JsonResponse
    {
        $evaluations = Evaluation::where('intervention_id', $interventionId)
            ->where('type_auteur', 'intervenant')
            ->with('critaire')
            ->get();

        // Get the comment for this intervention
        $comment = \DB::table('commentaire')
            ->where('intervention_id', $interventionId)
            ->where('type_auteur', 'intervenant')
            ->first();

        return response()->json([
            'evaluations' => $evaluations,
            'comment' => $comment ? $comment->commentaire : ''
        ]);
    }

    /**
     * Check if intervenant can rate client for this intervention.
     */
    public function canRateClient(int $interventionId): JsonResponse
    {
        try {
            $intervention = Intervention::find($interventionId);
            
            if (!$intervention) {
                return response()->json(['can_rate' => false, 'reason' => 'Intervention non trouvée']);
            }

            if ($intervention->status !== 'termine') {
                return response()->json(['can_rate' => false, 'reason' => 'Intervention non terminée. Statut actuel: ' . $intervention->status]);
            }

            $currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['can_rate' => false, 'reason' => 'Utilisateur non authentifié']);
            }

            // Get the intervenant record associated with this user
            $intervenant = $currentUser->intervenant;
            if (!$intervenant) {
                return response()->json(['can_rate' => false, 'reason' => 'Utilisateur n\'est pas un intervenant']);
            }

            if ($intervenant->id !== $intervention->intervenant_id) {
                return response()->json(['can_rate' => false, 'reason' => 'Non autorisé - Vous n\'êtes pas l\'intervenant assigné']);
            }

            // Check if within 7-day voting window
            $interventionDate = $intervention->updated_at;
            $sevenDaysAgo = now()->subDays(7);
            $isWithinWindow = $interventionDate->greaterThan($sevenDaysAgo);

            // Check existing ratings
            $existingRatings = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'intervenant')
                ->count();

            // Check if evaluations are public (read from database)
            $arePublic = Evaluation::where('intervention_id', $interventionId)
                ->where('is_public', true)
                ->exists();

            // Determine voting status
            if (!$isWithinWindow && $existingRatings === 0) {
                return response()->json([
                    'can_rate' => false, 
                    'can_view' => false,
                    'reason' => 'Délai de 7 jours dépassé - Impossible d\'évaluer',
                    'status' => 'expired'
                ]);
            }

            if ($existingRatings > 0) {
                return response()->json([
                    'can_rate' => false, 
                    'can_view' => true,
                    'reason' => 'Déjà évalué',
                    'status' => 'view_only',
                    'is_public' => $arePublic
                ]);
            }

            if (!$isWithinWindow) {
                return response()->json([
                    'can_rate' => false, 
                    'can_view' => false,
                    'reason' => 'Délai de 7 jours dépassé',
                    'status' => 'expired'
                ]);
            }

            return response()->json([
                'can_rate' => true, 
                'can_view' => false,
                'status' => 'can_rate',
                'days_remaining' => $interventionDate->diffInDays(now())
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in canRateClient:', ['error' => $e->getMessage()]);
            return response()->json([
                'can_rate' => false, 
                'reason' => 'Erreur technique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public evaluations for an intervention (when both parties have voted).
     */
    public function getPublicEvaluations(int $interventionId): JsonResponse
    {
        try {
            $intervention = Intervention::findOrFail($interventionId);

            // Check if there are any public evaluations for this intervention
            $hasPublicEvaluations = Evaluation::where('intervention_id', $interventionId)
                ->where('is_public', true)
                ->exists();

            if (!$hasPublicEvaluations) {
                return response()->json([
                    'message' => 'Les évaluations ne sont pas encore publiques',
                    'can_view' => false
                ], 403);
            }

            // Fetch only public evaluations
            $intervenantRatings = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'intervenant')
                ->where('is_public', true)
                ->with('critaire')
                ->get();

            $clientRatings = Evaluation::where('intervention_id', $interventionId)
                ->where('type_auteur', 'client')
                ->where('is_public', true)
                ->with('critaire')
                ->get();

            $bothPartiesVoted = $intervenantRatings->count() > 0 && $clientRatings->count() > 0;
            $windowPassed = $intervention->updated_at->addDays(7)->isPast();

            // Get public comments only
            $intervenantComment = Commentaire::where('intervention_id', $interventionId)
                ->where('type_auteur', 'intervenant')
                ->where('is_public', true)
                ->first();

            $clientComment = Commentaire::where('intervention_id', $interventionId)
                ->where('type_auteur', 'client')
                ->where('is_public', true)
                ->first();

            return response()->json([
                'can_view' => true,
                'both_parties_voted' => $bothPartiesVoted,
                'window_passed' => $windowPassed,
                'intervenant_ratings' => $intervenantRatings,
                'client_ratings' => $clientRatings,
                'intervenant_comment' => $intervenantComment,
                'client_comment' => $clientComment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des évaluations publiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get average client rating for a specific client.
     */
    public function getClientAverageRating(int $clientId): JsonResponse
    {
        // Get completed interventions for this client
        $clientInterventions = Intervention::where('client_id', $clientId)
            ->where('status', 'termine')
            ->pluck('id');

        // Get only public evaluations from intervenant
        $evaluations = Evaluation::whereIn('intervention_id', $clientInterventions)
            ->where('type_auteur', 'intervenant')
            ->where('is_public', true)
            ->with('critaire')
            ->get();

        if ($evaluations->isEmpty()) {
            return response()->json([
                'average_rating' => 0,
                'total_evaluations' => 0,
                'ratings_by_criteria' => []
            ]);
        }

        $ratingsByCriteria = $evaluations->groupBy('critaire_id')
            ->map(function ($group) {
                return [
                    'criteria' => $group->first()->critaire->nom_critaire,
                    'average' => $group->avg('note'),
                    'count' => $group->count()
                ];
            });

        return response()->json([
            'average_rating' => $evaluations->avg('note'),
            'total_evaluations' => $evaluations->count(),
            'ratings_by_criteria' => $ratingsByCriteria
        ]);
    }
}
