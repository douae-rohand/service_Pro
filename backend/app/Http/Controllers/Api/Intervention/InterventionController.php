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
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return $query;
        }

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isIntervenant()) {
            $query->where('intervenant_id', $user->id);
        } else {
            // Unknown role? limit access
            $query->where('id', -1);
        }
        
        return $query;
    }

    /**
     * Display a listing of interventions
     */
    public function index(Request $request)
    {
        $query = Intervention::with(['client.utilisateur', 'intervenant.utilisateur', 'tache', 'photos']);

        // Secure: Apply User Scope
        $this->scopeQueryProprety($query);

        // Filtrer par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Additional filters are fine as long as they don't broaden the scope beyond the user's rights
        if ($request->has('clientId')) {
            $query->where('client_id', $request->clientId);
        }

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
            'tacheId' => 'required|exists:tache,id',
        ]);

        // Auto-fix ID for client if creating their own
        $user = Auth::guard('sanctum')->user();
        
        // If user is client, ensure they are booking for themselves unless admin (logic typically requires this)
        // We permit it for now but in strict mode we would overwrite client_id
        if ($user && $user->isClient()) {
            $validated['clientId'] = $user->id;
        }

        $data = [
            'address' => $validated['address'],
            'ville' => $validated['ville'],
            'status' => $validated['status'] ?? 'en attend',
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

        // Privacy Filter
        $isPublic = $intervention->areRatingsPublic();
        $currentUser = Auth::user();
        
        // Access Control
        if ($currentUser) {
            $canAccess = ($currentUser->isClient() && $currentUser->id == $intervention->client_id) ||
                         ($currentUser->isIntervenant() && $currentUser->id == $intervention->intervenant_id);
                         
            if (!$canAccess) {
                 abort(403, 'Accès non autorisé.');
            }
        } else {
            abort(401, 'Non authentifié.');
        }

        $isAuthorClient = $currentUser && $currentUser->isClient() && $currentUser->id === $intervention->client_id;
        $isAuthorIntervenant = $currentUser && $currentUser->isIntervenant() && $currentUser->id === $intervention->intervenant_id;

        if (!$isPublic) {
            $intervention->setRelation('evaluations', $intervention->evaluations->filter(function ($e) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $e->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $e->type_auteur === 'intervenant') return true;
                return false;
            }));

            $intervention->setRelation('commentaires', $intervention->commentaires->filter(function ($c) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $c->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $c->type_auteur === 'intervenant') return true;
                return false;
            }));
        }

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
        $user = Auth::guard('sanctum')->user();

        // Check ownership
        if (!$user) {
            abort(401);
        }
        
        $canEdit = ($user->isClient() && $user->id == $intervention->client_id) ||
                   ($user->isIntervenant() && $user->id == $intervention->intervenant_id);

        if (!$canEdit) {
             abort(403, "Vous n'êtes pas autorisé à modifier cette intervention.");
        }

        $validated = $request->validate([
            'address' => 'sometimes|string',
            'ville' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:en attend,acceptee,refuse,termine',
            'dateIntervention' => 'sometimes|date',
            'clientId' => 'sometimes|exists:client,id',
            'intervenantId' => 'sometimes|exists:intervenant,id',
            'tacheId' => 'sometimes|exists:tache,id',
        ]);

        $data = [];
        if (isset($validated['address'])) $data['address'] = $validated['address'];
        if (isset($validated['ville'])) $data['ville'] = $validated['ville'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];
        if (isset($validated['dateIntervention'])) $data['date_intervention'] = $validated['dateIntervention'];
        if (isset($validated['clientId'])) $data['client_id'] = $validated['clientId'];
        if (isset($validated['intervenantId'])) $data['intervenant_id'] = $validated['intervenantId'];
        if (isset($validated['tacheId'])) $data['tache_id'] = $validated['tacheId'];

        if (empty($data)) {
            return response()->json(['message' => 'Aucune modification détectée'], 200);
        }

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
        $user = Auth::guard('sanctum')->user();
        
        // Only owner can delete (or admin)
        if (!$user || !($user->isClient() && $user->id == $intervention->client_id)) {
            abort(403, "Action non autorisée");
        }
        
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
        $query = Intervention::upcoming()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);

        $this->scopeQueryProprety($query);

        return response()->json($query->get());
    }

    /**
     * Get completed interventions
     */
    public function completed()
    {
        $query = Intervention::completed()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);
            
        $this->scopeQueryProprety($query);

        return response()->json($query->orderBy('date_intervention', 'desc')->paginate(15));
    }

    /**
     * Add a photo to an intervention
     */
    public function addPhoto(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        
        $canEdit = ($user->isClient() && $user->id == $intervention->client_id) ||
                   ($user->isIntervenant() && $user->id == $intervention->intervenant_id);

        if (!$canEdit) {
             abort(403, "Accès refusé.");
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string',
            'phase_prise' => 'nullable|string|in:avant,apres',
        ]);

        $phase = $validated['phase_prise'] ?? ($intervention->status === 'termine' ? 'apres' : 'avant');
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
