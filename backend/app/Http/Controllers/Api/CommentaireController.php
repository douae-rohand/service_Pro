<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    /**
     * Récupérer les témoignages pour la landing page
     */
    public function landingTestimonials()
    {
        try {
            // Récupérer les 3 derniers commentaires des clients
            $commentaires = Commentaire::where('type_auteur', 'client')
                ->with(['intervention.client.utilisateur'])
                ->latest()
                ->take(3) // Prendre les 3 derniers
                ->get();

            $formattedTestimonials = $commentaires->filter(function ($commentaire) {
                // Filtrer les commentaires qui ont une intervention et un client valides
                return $commentaire->intervention && $commentaire->intervention->client;
            })->map(function ($commentaire) {
                $client = $commentaire->intervention->client ?? null;
                $user = $client ? $client->utilisateur : null;
                
                // Essayer de trouver une note associée à cette intervention
                $rating = 5; // Valeur par défaut
                
                // Chercher l'évaluation liée à la même intervention
                if ($commentaire->intervention_id) {
                    $evaluation = \App\Models\Evaluation::where('intervention_id', $commentaire->intervention_id)
                        ->where('type_auteur', 'client')
                        ->first();
                        
                    if ($evaluation) {
                        $rating = $evaluation->note;
                    }
                }

                // Générer initiale
                $initial = 'C';
                if ($user && $user->prenom) {
                    $initial = substr(strtoupper($user->prenom), 0, 1);
                }
                
                return [
                    'id' => $commentaire->id,
                    'name' => $user ? ($user->prenom . ' ' . $user->nom) : 'Client Anonyme',
                    'initial' => $initial,
                    'rating' => $rating,
                    'text' => $commentaire->commentaire,
                    'date' => $commentaire->created_at
                ];
            })->values(); // Réindexer le tableau

            return response()->json($formattedTestimonials);
        } catch (\Exception $e) {
            \Log::error('Erreur dans CommentaireController::landingTestimonials: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Retourner un tableau vide en cas d'erreur
            return response()->json([], 200);
        }
    }
}
