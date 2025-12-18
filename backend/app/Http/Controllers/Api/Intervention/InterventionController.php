<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\PhotoIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Intervenant;

class InterventionController extends Controller
{
    /**
     * Helper to scope queries to the authenticated user
     */
    private function scopeQueryProprety($query)
    {
        $user = Auth::user();

        if (!$user) {
            return $query; // Or handle as error/empty
        }

        if ($user->userable_type === Client::class || $user->userable_type === 'App\Models\Client') {
            $query->where('client_id', $user->userable_id);
        } elseif ($user->userable_type === Intervenant::class || $user->userable_type === 'App\Models\Intervenant') {
            $query->where('intervenant_id', $user->userable_id);
        }
        
        return $query;
    }

    /**
     * Display a listing of interventions
     */
    public function index(Request $request)
    {
        $query = Intervention::with(['client.utilisateur', 'intervenant.utilisateur', 'tache', 'photos']);

        // Filtrer par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par client si fourni
        if ($request->has('clientId')) {
            $query->where('client_id', $request->clientId);
        }

        // Filtrer par intervenant si fourni
        if ($request->has('intervenantId')) {
            $query->where('intervenant_id', $request->intervenantId);
        }

        $interventions = $query->orderBy('date_intervention', 'desc')->paginate(15);

        return response()->json($interventions);
    }

    /**
     * Store a newly created intervention
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string',
            'ville' => 'required|string|max:100',
            'status' => 'nullable|in:en attend,acceptee,refuse,termine',
            'dateIntervention' => 'required|date',
            'clientId' => 'required|exists:client,id',
            'intervenantId' => 'required|exists:intervenant,id',
            'tache_id' => 'required|exists:tache,id',
        ]);

        // Convertir les noms camelCase en snake_case pour correspondre aux colonnes de la base de données
        $data = [
            'address' => $validated['address'],
            'ville' => $validated['ville'],
            'status' => $validated['status'] ?? null,
            'date_intervention' => $validated['dateIntervention'],
            'client_id' => $validated['clientId'],
            'intervenant_id' => $validated['intervenantId'],
            'tache_id' => $validated['tacheId'],
        ];

        $intervention = Intervention::create($data);

        return response()->json([
            'message' => 'Intervention créée avec succès',
            'intervention' => $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache']),
        ], 201);
    }

    /**
     * Display the specified intervention
     */
    public function show($id)
    {
        $intervention = Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'tache.materiels', 
            'photos',
            'evaluations.critaire',
            'commentaires',
            'facture',
            'materiels',
            'informations'
        ])->findOrFail($id);

        // Privacy Filter for evaluations and comments
        $isPublic = $intervention->areRatingsPublic();
        $currentUser = Auth::user();
        
        // Determine the role of the current user for this specific intervention
        $isAuthorClient = $currentUser && $currentUser->userable_type === 'App\Models\Client' && $currentUser->userable_id === $intervention->client_id;
        $isAuthorIntervenant = $currentUser && $currentUser->userable_type === 'App\Models\Intervenant' && $currentUser->userable_id === $intervention->intervenant_id;

        if (!$isPublic) {
            // Filter evaluations: Only keep ones written by current user
            $intervention->setRelation('evaluations', $intervention->evaluations->filter(function ($e) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $e->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $e->type_auteur === 'intervenant') return true;
                return false;
            }));

            // Filter comments
            $intervention->setRelation('commentaires', $intervention->commentaires->filter(function ($c) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $c->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $c->type_auteur === 'intervenant') return true;
                return false;
            }));
        }

        // Add additional metadata for the frontend to explain the privacy state
        $intervention->ratings_meta = [
            'is_public' => $isPublic,
            'client_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'client')->exists(),
            'intervenant_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'intervenant')->exists(),
            'window_expiry' => $intervention->status === 'termine' ? $intervention->updated_at->addDays(7)->toDateTimeString() : null
        ];

        return response()->json($intervention);
    }

    /**
     * Update the specified intervention
     */
    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validated = $request->validate([
            'address' => 'sometimes|string',
            'ville' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:en attend,acceptee,refuse,termine',
            'dateIntervention' => 'sometimes|date',
            'clientId' => 'sometimes|exists:client,id',
            'intervenantId' => 'sometimes|exists:intervenant,id',
            'tacheId' => 'sometimes|exists:tache,id',
        ]);

        // Convertir les noms camelCase en snake_case pour correspondre aux colonnes de la base de données
        $data = [];
        if (isset($validated['address'])) $data['address'] = $validated['address'];
        if (isset($validated['ville'])) $data['ville'] = $validated['ville'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];
        if (isset($validated['dateIntervention'])) $data['date_intervention'] = $validated['dateIntervention'];
        if (isset($validated['clientId'])) $data['client_id'] = $validated['clientId'];
        if (isset($validated['intervenantId'])) $data['intervenant_id'] = $validated['intervenantId'];
        if (isset($validated['tacheId'])) $data['tache_id'] = $validated['tacheId'];

        $intervention->update($data);

        return response()->json([
            'message' => 'Intervention mise à jour avec succès',
            'intervention' => $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache']),
        ]);
    }

    /**
     * Remove the specified intervention
     */
    public function destroy($id)
    {
        $intervention = Intervention::findOrFail($id);
        $intervention->delete();

        return response()->json([
            'message' => 'Intervention supprimée avec succès',
        ]);
    }

    /**
     * Get upcoming interventions
     */
    public function upcoming()
    {
        $interventions = Intervention::upcoming()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache'])
            ->get();

        return response()->json($interventions);
    }

    /**
     * Get completed interventions
     */
    public function completed()
    {
        $interventions = Intervention::completed()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache'])
            ->orderBy('date_intervention', 'desc')
            ->paginate(15);

        return response()->json($interventions);
    }

    /**
     * Add a photo to an intervention
     */
    public function addPhoto(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Increased to 5MB
            'description' => 'nullable|string',
            'phase_prise' => 'nullable|string|in:avant,apres',
        ]);

        // Déterminer la phase par défaut si non fournie
        $phase = $validated['phase_prise'] ?? ($intervention->status === 'termine' ? 'apres' : 'avant');

        // Stocker la photo
        $path = $request->file('photo')->store('interventions', 'public');

        $photo = PhotoIntervention::create([
            'intervention_id' => $intervention->id,
            'photo_path' => $path,
            'phase_prise' => $phase,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Photo ajoutée avec succès',
            'photo' => $photo,
        ], 201);
    }
}
