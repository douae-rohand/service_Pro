<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Tache;
use Illuminate\Http\Request;

class TacheController extends Controller
{
    /**
     * Display a listing of taches
     */
    public function index(Request $request)
    {
        $query = Tache::with(['service', 'materiels', 'intervenants']);

        // Filtrer par service si fourni
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $taches = $query->get();

        return response()->json([
        'status' => 'success',
        'data' => $taches
    ]);
    }

    /**
     * Store a newly created tache
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:service,id',
            'nom_tache' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        $tache = Tache::create($validated);

        return response()->json([
        'status' => 'success',
        'message' => 'Tâche créée avec succès',
        'data' => $tache->load('service')
    ], 201);
    }

    /**
     * Display the specified tache
     */
    public function show($id)
    {
        $tache = Tache::with(['service', 'materiels', 'intervenants', 'interventions'])->findOrFail($id);

        return response()->json([
        'status' => 'success',
        'data' => $tache
    ]);
    }

    public function getContraintes($id)
    {
        $tache = Tache::with('contraintes')->findOrFail($id);
        return response()->json([
        'status' => 'success',
        'data' => $tache->contraintes
    ]);
    }

    /**
     * Update the specified tache
     */
    public function update(Request $request, $id)
    {
        $tache = Tache::findOrFail($id);

        $validated = $request->validate([
            'service_id' => 'sometimes|exists:service,id',
            'nom_tache' => 'sometimes|string|max:150',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string|max:50',
        ]);

        $tache->update($validated);

        return response()->json([
        'status' => 'success',
        'message' => 'Tâche mise à jour avec succès',
        'data' => $tache->load('service')
    ]);
    }

    /**
     * Remove the specified tache
     */
    public function destroy($id)
    {
        $tache = Tache::findOrFail($id);
        $tache->delete();

        return response()->json([
        'status' => 'success',
        'message' => 'Tâche supprimée avec succès'
    ]);
    }

    public function getMateriels($id)
    {
        try {
            $tache = Tache::with('materiels')->findOrFail($id);
            // Utilisez la relation belongsToMany sans parenthèses get()
            $materiels = $tache->materiels;
            
            return response()->json([
                'status' => 'success',
                'data' => $materiels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des matériaux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get intervenants for a specific tache
     */
    public function getIntervenants($id)
    {
        try {
            \Log::info('[TacheController] Fetching intervenants for task ID: ' . $id);
            
            $tache = Tache::findOrFail($id);
            
            // Query intervenants directly via the relationship
            $intervenants = $tache->intervenants()
                ->where('is_active', true) // Only active ones
                ->with(['utilisateur']) // Essential
                ->withAvg('evaluations', 'note')
                ->withCount(['evaluations', 'interventions'])
                ->get();
            
            \Log::info('[TacheController] Found ' . $intervenants->count() . ' active intervenants for task ' . $id);
            
            // Transform data for frontend
            $client = \App\Models\Client::where('id', auth()->id())->first();
            $favoriteIds = $client ? $client->intervenantsFavoris()->pluck('intervenant_id')->toArray() : [];

            $transformed = $intervenants->map(function ($intervenant) use ($favoriteIds) {
                // Return exactly what SearchServices.vue expects
                return [
                    'id' => $intervenant->id,
                    'utilisateur' => $intervenant->utilisateur,
                    'average_rating' => $intervenant->evaluations_avg_note ? round($intervenant->evaluations_avg_note, 1) : 4.5,
                    'review_count' => $intervenant->evaluations_count ?? 0,
                    'missions_completees' => $intervenant->interventions_count ?? 0,
                    'ville' => $intervenant->ville,
                    'address' => $intervenant->address,
                    'is_active' => $intervenant->is_active,
                    'tarif_tache' => $intervenant->pivot->prix_tache ?? null,
                    'status_tache' => $intervenant->pivot->status ?? 'actif',
                    'is_favorite' => in_array($intervenant->id, $favoriteIds)
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $transformed,
                'meta' => [
                    'task_id' => $id,
                    'count' => $transformed->count()
                ]
            ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Task not found: ' . $id);
        
        // Standardized 404 response
        return response()->json([
            'status' => 'error',
            'message' => 'Tâche non trouvée'
        ], 404);
        
    } catch (\Exception $e) {
        \Log::error('Error in getIntervenants: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        // Standardized error response
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de la récupération des intervenants',
            'details' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
}
