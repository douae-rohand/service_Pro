<?php

namespace App\Http\Controllers\Api\Evaluation;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Intervention;
use App\Models\Commentaire;
use App\Models\Critaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            // Get all criteria
            $criteria = Critaire::all();
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
            foreach ($validated['criteriaRatings'] as $criteriaName => $note) {
                $critaireId = $criteriaMap[$criteriaName] ?? null;
                
                if ($critaireId) {
                    Evaluation::create([
                        'intervention_id' => $interventionId,
                        'critaire_id' => $critaireId,
                        'note' => $note,
                        'type_auteur' => 'client',
                    ]);
                }
            }

            // Create comment if provided
            if (!empty($validated['comment'])) {
                Commentaire::create([
                    'intervention_id' => $interventionId,
                    'commentaire' => $validated['comment'],
                    'type_auteur' => 'client',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Évaluation soumise avec succès',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
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
}

