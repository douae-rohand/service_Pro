<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

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
}
