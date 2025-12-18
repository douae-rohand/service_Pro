<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $query = Client::with(['utilisateur', 'admin.utilisateur']);

        // Filtrer les clients actifs uniquement si demandé
        if ($request->has('active') && $request->active == 'true') {
            $query->active();
        }

        $clients = $query->get();

        return response()->json($clients);
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:utilisateur,id',
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client créé avec succès',
            'client' => $client->load('utilisateur'),
        ], 201);
    }

    /**
     * Display the specified client
     */
    public function show($id)
    {
        $client = Client::with(['utilisateur', 'admin.utilisateur', 'interventions', 'intervenantsFavoris.utilisateur'])
            ->findOrFail($id);

        return response()->json($client);
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Client mis à jour avec succès',
            'client' => $client->load('utilisateur'),
        ]);
    }

    /**
     * Remove the specified client
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès',
        ]);
    }

    /**
     * Get interventions for a specific client
     */
    public function interventions($id)
    {
        $client = Client::findOrFail($id);
        $interventions = $client->interventions()
            ->with(['intervenant.utilisateur', 'tache'])
            ->orderBy('dateIntervention', 'desc')
            ->get();

        return response()->json($interventions);
    }

    /**
     * Add an intervenant to favorites
     */
    public function addFavorite(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'intervenantId' => 'required|exists:intervenant,id',
        ]);

        $client->intervenantsFavoris()->syncWithoutDetaching([$validated['intervenantId']]);

        return response()->json([
            'message' => 'Intervenant ajouté aux favoris',
        ]);
    }

    /**
     * Remove an intervenant from favorites
     */
    public function removeFavorite($id, $intervenantId)
    {
        $client = Client::findOrFail($id);
        $client->intervenantsFavoris()->detach($intervenantId);

        return response()->json([
            'message' => 'Intervenant retiré des favoris',
        ]);
    }

    /**
     * Get client profile data for intervenant view
     * Includes ratings summary and past interventions between them
     */
    public function getProfileForIntervenant($interventionId)
    {
        // Find the intervention first to get the clientId and verify ownership
        $intervention = Intervention::with('client.utilisateur')->findOrFail($interventionId);
        $clientId = $intervention->client_id;
        $client = $intervention->client;
        
        $currentUser = Auth::user();
        $intervenant = $currentUser->intervenant;

        if (!$intervenant || $intervention->intervenant_id !== $intervenant->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Helper to find public intervention IDs for this client
        // Rule: Only public if BOTH parties have rated OR 7 days have passed (Logic centralized in model)
        $publicInterventionIds = Intervention::where('client_id', $clientId)
            ->where('status', 'termine')
            ->get()
            ->filter(function($i) {
                return $i->areRatingsPublic();
            })
            ->pluck('id');

        // Get ALL past interventions for this client from ALL intervenants
        $pastInterventions = Intervention::where('client_id', $clientId)
            ->where('status', 'termine')
            ->with(['tache.service', 'intervenant.utilisateur', 'evaluations' => function($q) {
                // We only want the ratings given BY the intervenant to the client
                $q->where('type_auteur', 'intervenant');
            }])
            ->orderBy('date_intervention', 'desc')
            ->get()
            ->filter(function ($i) use ($publicInterventionIds) {
                // STRICT RULE: Only show interventions in the history list that are PUBLIC.
                // This means even my own private ratings won't show up here until they are public.
                return $publicInterventionIds->contains($i->id);
            })
            ->map(function ($i) use ($publicInterventionIds, $intervenant) {
                $isPublic = true; // By definition, since we filtered above
                $isMine = $i->intervenant_id === $intervenant->id;
                
                // Get the ratings given by the intervenant for this specific intervention
                $ratings = $i->evaluations->map(function($e) {
                    return [
                        'criteria' => $e->critaire->nom_critaire,
                        'note' => $e->note
                    ];
                });
                
                $ratingAvg = $i->evaluations->avg('note') ?? 0;

                return [
                    'id' => $i->id,
                    'date' => $i->date_intervention,
                    'service' => $i->tache->service->nom_service ?? 'Service',
                    'task' => $i->tache->nom_tache ?? 'Tâche',
                    'status' => $i->status,
                    'intervenant_name' => $isMine ? 'Vous' : ($i->intervenant->utilisateur->nom . ' ' . $i->intervenant->utilisateur->prenom),
                    'is_me' => $isMine,
                    'rating' => round($ratingAvg, 1),
                    'detailed_ratings' => $ratings,
                    'has_evaluation' => $i->evaluations->isNotEmpty(),
                    'is_public' => $isPublic
                ];
            })
            ->values(); // Reset keys after filter

        // Get all PUBLIC evaluations made by ANY intervenant for this client
        $allClientEvaluations = Evaluation::whereIn('intervention_id', $publicInterventionIds)
            ->where('type_auteur', 'intervenant')
            ->with('critaire')
            ->get();

        // Calculate overall rating from public evaluations
        $overallAverage = $allClientEvaluations->avg('note') ?? 0;
        $totalRatedInterventions = $allClientEvaluations->pluck('intervention_id')->unique()->count();

        // Calculate average per criteria from public evaluations
        $criteriaAverages = $allClientEvaluations->groupBy('critaire_id')->map(function ($evaluations) {
            $critaire = $evaluations->first()->critaire;
            return [
                'id' => $critaire->id,
                'nom' => $critaire->nom_critaire,
                'average' => round($evaluations->avg('note'), 1)
            ];
        })->values();

        // Get PUBLIC comments from ALL intervenants about this client
        $comments = DB::table('commentaire')
            ->join('intervention', 'commentaire.intervention_id', '=', 'intervention.id')
            ->join('intervenant', 'intervention.intervenant_id', '=', 'intervenant.id')
            ->join('utilisateur', 'intervenant.id', '=', 'utilisateur.id')
            ->where('intervention.client_id', $clientId)
            ->whereIn('intervention.id', $publicInterventionIds)
            ->where('commentaire.type_auteur', 'intervenant')
            ->select(
                'commentaire.commentaire', 
                'commentaire.created_at', 
                'intervention.date_intervention',
                'utilisateur.nom',
                'utilisateur.prenom'
            )
            ->orderBy('intervention.date_intervention', 'desc')
            ->get()
            ->map(function($comment) {
                return [
                    'commentaire' => $comment->commentaire,
                    'date_intervention' => $comment->date_intervention,
                    'author' => $comment->nom . ' ' . $comment->prenom
                ];
            });

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->utilisateur->nom . ' ' . $client->utilisateur->prenom,
                'email' => $client->utilisateur->email,
                'phone' => $client->utilisateur->numTel ?? 'Non renseigné',
                'address' => $client->address ?? 'Non renseignée',
                'ville' => $client->ville ?? 'Non renseignée',
                'photo' => $client->utilisateur->profilePicture ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
                'member_since' => $client->created_at
            ],
            'rating_summary' => [
                'overall_average' => round($overallAverage, 1),
                'total_rated_interventions' => $totalRatedInterventions,
                'criteria_averages' => $criteriaAverages
            ],
            'past_interventions' => $pastInterventions,
            'comments' => $comments
        ]);
    }
}
