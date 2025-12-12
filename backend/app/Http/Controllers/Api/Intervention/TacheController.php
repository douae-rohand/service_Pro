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
            $query->where('service_id', $request->serviceId);
        }

        $taches = $query->get();

        return response()->json($taches);
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
            'message' => 'Tâche créée avec succès',
            'tache' => $tache->load('service'),
        ], 201);
    }

    /**
     * Display the specified tache
     */
    public function show($id)
    {
        $tache = Tache::with(['service', 'materiels', 'intervenants', 'interventions'])->findOrFail($id);

        return response()->json($tache);
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
            'message' => 'Tâche mise à jour avec succès',
            'tache' => $tache->load('service'),
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
            'message' => 'Tâche supprimée avec succès',
        ]);
    }

    /**
     * Get intervenants for a specific tache
     */
    public function getIntervenants($id)
    {
        $tache = Tache::findOrFail($id);
        $intervenants = $tache->intervenants()->with('utilisateur')->get();

        // Add statistics to each intervenant
        $intervenants->each(function ($intervenant) {
            // Count completed interventions
            $intervenant->missions_completees = $intervenant->interventions()
                ->where('status', 'terminée')
                ->count();
            
            // Calculate average rating from evaluations
            $avgRating = $intervenant->interventions()
                ->whereHas('evaluation')
                ->with('evaluation')
                ->get()
                ->pluck('evaluation.note')
                ->avg();
            
            $intervenant->note_moyenne = $avgRating ? round($avgRating, 1) : null;
            
            // Count total reviews
            $intervenant->nombre_avis = $intervenant->interventions()
                ->whereHas('evaluation')
                ->count();
        });

        return response()->json([
            'intervenants' => $intervenants
        ]);
    }
}
