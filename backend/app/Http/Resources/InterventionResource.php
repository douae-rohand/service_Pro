<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterventionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Map database status to frontend status
        $statusMap = [
            'en_attente' => 'pending',
            'en attente' => 'pending',
            'acceptee' => 'accepted',
            'acceptée' => 'accepted',
            'acceptées' => 'accepted',
            'en_cours' => 'in-progress',
            'en cours' => 'in-progress',
            'terminee' => 'completed',
            'terminée' => 'completed',
            'terminées' => 'completed',
            'annulee' => 'cancelled',
            'annulée' => 'cancelled',
            'annulées' => 'cancelled',
            'refusee' => 'rejected',
            'refusée' => 'rejected',
            'refusées' => 'rejected',
            'planifiee' => 'accepted',
            'planifiée' => 'accepted',
        ];

        $status = strtolower($this->status ?? 'en_attente');
        $mappedStatus = $statusMap[$status] ?? $status;

        // Get client evaluations
        $clientEvaluations = $this->evaluations->where('type_auteur', 'client');
        $rating = null;
        
        if ($clientEvaluations->count() > 0) {
            $criteriaRatings = [];
            $totalRating = 0;
            
            foreach ($clientEvaluations as $evaluation) {
                $criteriaName = strtolower(str_replace(' ', '_', $evaluation->critaire->nom_critaire ?? 'quality'));
                // Map French criteria names to English keys
                $criteriaNameMap = [
                    'qualité_du_travail' => 'quality',
                    'ponctualité' => 'punctuality',
                    'professionnalisme' => 'professionalism',
                    'communication' => 'communication',
                    'propreté' => 'cleanup',
                    'rapport_qualité/prix' => 'value',
                ];
                $criteriaName = $criteriaNameMap[$criteriaName] ?? $criteriaName;
                $criteriaRatings[$criteriaName] = $evaluation->note ?? 0;
                $totalRating += $evaluation->note ?? 0;
            }
            
            $commentaire = $this->commentaires->where('type_auteur', 'client')->first();
            
            $rating = [
                'criteriaRatings' => $criteriaRatings,
                'overallRating' => round($totalRating / $clientEvaluations->count(), 1),
                'comment' => $commentaire->commentaire ?? '',
                'wouldRecommend' => true // TODO: Add this field to database if needed
            ];
        }

        // Get intervenant response
        $intervenantResponse = $this->commentaires
            ->where('type_auteur', 'intervenant')
            ->first()
            ->commentaire ?? null;

        // Get intervenant average rating
        $intervenantEvaluations = \App\Models\Evaluation::whereHas('intervention', function($query) {
            $query->where('intervenant_id', $this->intervenant_id);
        })->where('type_auteur', 'client')->get();

        $averageRating = null;
        $totalReviews = 0;
        $ratingDistribution = [];

        if ($intervenantEvaluations->count() > 0) {
            $totalReviews = $intervenantEvaluations->count();
            $totalNotes = $intervenantEvaluations->sum('note');
            $averageRating = round($totalNotes / $totalReviews, 1);

            // Calculate rating distribution
            for ($i = 1; $i <= 5; $i++) {
                $ratingDistribution[$i] = $intervenantEvaluations->where('note', $i)->count();
            }
        }

        // Transform invoice
        $invoice = null;
        if ($this->facture) {
            $invoice = [
                'date' => $this->facture->created_at?->format('Y-m-d'),
                'actualDuration' => '2 heures', // TODO: Calculate from actual data if available
                'laborCost' => $this->facture->ttc ?? 0, // TODO: Separate labor and materials
                'materialsProvided' => [], // TODO: Extract from intervention_materiel with costs
                'materialsCost' => 0, // TODO: Calculate from materials
                'paymentDate' => $this->facture->created_at?->format('Y-m-d')
            ];
        }

        // Transform materials
        $materials = [];
        if ($this->materiels) {
            foreach ($this->materiels as $materiel) {
                $materials[$materiel->nom_materiel] = true; // All materials in intervention are available
            }
        }

        // Transform photos
        $photos = [];
        if ($this->photos) {
            foreach ($this->photos as $photo) {
                $photoPath = $photo->photo_path ?? $photo->url ?? null;
                if ($photoPath) {
                    // If it's a relative path, prepend storage URL
                    if (!str_starts_with($photoPath, 'http')) {
                        $photos[] = asset('storage/' . ltrim($photoPath, '/'));
                    } else {
                        $photos[] = $photoPath;
                    }
                }
            }
        }

        // Get estimated cost from intervenant_tache
        $estimatedCost = 0;
        if ($this->tache && $this->intervenant_id) {
            $intervenantTache = \App\Models\IntervenantTache::where('tache_id', $this->tache_id)
                ->where('intervenant_id', $this->intervenant_id)
                ->first();
            $estimatedCost = $intervenantTache ? (float)($intervenantTache->prix_tache ?? 0) : 0;
        }

        // Get description from intervention_information or tache
        $description = $this->tache->description ?? '';
        if ($this->informations && $this->informations->count() > 0) {
            $infoParts = [];
            foreach ($this->informations as $info) {
                $infoParts[] = $info->pivot->information ?? '';
            }
            if (!empty($infoParts)) {
                $description = implode(', ', array_filter($infoParts));
            }
        }

        return [
            'id' => $this->id,
            'service' => $this->tache->service->nom_service ?? ($this->tache->service_id ? 'N/A' : 'N/A'),
            'task' => $this->tache->nom_tache ?? 'N/A',
            'intervenant' => [
                'id' => $this->intervenant->id ?? null,
                'name' => trim(($this->intervenant->utilisateur->prenom ?? '') . ' ' . ($this->intervenant->utilisateur->nom ?? '')) ?: 'N/A',
                'image' => $this->intervenant->utilisateur->url ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
                'phone' => $this->intervenant->utilisateur->telephone ?? '',
                'averageRating' => $averageRating,
                'totalReviews' => $totalReviews,
                'ratingDistribution' => $ratingDistribution,
            ],
            'date' => $this->date_intervention ? (\Carbon\Carbon::parse($this->date_intervention)->format('Y-m-d')) : null,
            'time' => '09:00', // TODO: Add time field to database if needed
            'status' => $mappedStatus,
            'address' => $this->address ?? '',
            'quartier' => $this->ville ?? '',
            'estimatedCost' => $estimatedCost,
            'finalCost' => $this->facture->ttc ?? null,
            'duration' => null, // TODO: Calculate or store duration
            'description' => $description,
            'createdAt' => $this->created_at?->format('Y-m-d'),
            'completedAt' => $mappedStatus === 'completed' ? ($this->updated_at?->format('Y-m-d')) : null,
            'intervenantResponse' => $intervenantResponse,
            'rating' => $rating,
            'invoice' => $invoice,
            'materials' => $materials,
            'photos' => $photos,
        ];
    }
}

