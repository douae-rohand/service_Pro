<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\PhotoIntervention;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
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
            'tacheId' => 'required|exists:tache,id',
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
            'photos',
            'evaluations.critaire',
            'commentaires',
            'facture',
            'materiels',
            'informations'
        ])->findOrFail($id);

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
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        // Stocker la photo
        $path = $request->file('photo')->store('interventions', 'public');

        $photo = PhotoIntervention::create([
            'intervention_id' => $intervention->id,
            'photo_path' => $path,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Photo ajoutée avec succès',
            'photo' => $photo,
        ], 201);
    }
}
