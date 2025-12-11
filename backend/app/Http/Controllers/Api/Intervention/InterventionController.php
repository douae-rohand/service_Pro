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
            $query->where('clientId', $request->clientId);
        }

        // Filtrer par intervenant si fourni
        if ($request->has('intervenantId')) {
            $query->where('intervenantId', $request->intervenantId);
        }

        $interventions = $query->orderBy('dateIntervention', 'desc')->paginate(15);

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
            'status' => 'nullable|string|max:50',
            'dateIntervention' => 'required|date',
            'clientId' => 'required|exists:client,id',
            'intervenantId' => 'required|exists:intervenant,id',
            'tacheId' => 'required|exists:tache,id',
        ]);

        $intervention = Intervention::create($validated);

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
            'status' => 'sometimes|string|max:50',
            'dateIntervention' => 'sometimes|date',
            'clientId' => 'sometimes|exists:client,id',
            'intervenantId' => 'sometimes|exists:intervenant,id',
            'tacheId' => 'sometimes|exists:tache,id',
        ]);

        $intervention->update($validated);

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
            ->orderBy('dateIntervention', 'desc')
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
            'interventionId' => $intervention->id,
            'url' => $path,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Photo ajoutée avec succès',
            'photo' => $photo,
        ], 201);
    }
}
