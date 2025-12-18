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

    public function getContraintes($id)
    {
        $tache = Tache::with('contraintes')->findOrFail($id);
        return response()->json($tache->contraintes);
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

    public function getMateriels($id)
    {
        try {
            $tache = Tache::findOrFail($id);
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
            \Log::info('📋 Fetching intervenants for task ID: ' . $id);
            
            // ⭐ CORRECTION CRUCIALE : Retirez ".evaluation" des relations
            $tache = Tache::with([
                'intervenants' => function($query) {
                    $query->with('utilisateur')
                          ->withAvg('evaluations', 'note')
                          ->withCount('evaluations');
                },
                'intervenants.taches.service',
                'intervenants.interventions.evaluation'
            ])->findOrFail($id);
            
            $intervenants = $tache->intervenants;
            
            \Log::info('✅ Found ' . $intervenants->count() . ' intervenants');
            
            // Ajoutez des données calculées pour le frontend
            $intervenants->transform(function ($intervenant) {
                // Use real aggregates if available, fallback to defaults
                // 'evaluations_avg_note' is automatically added by withAvg
                // 'evaluations_count' is automatically added by withCount
                
                $realRating = $intervenant->evaluations_avg_note ? round($intervenant->evaluations_avg_note, 1) : null;
                $realCount = $intervenant->evaluations_count;

                $intervenant->note_moyenne = $realRating ?? $intervenant->average_rating ?? 4.5;
                $intervenant->nombre_avis = $realCount ?? $intervenant->review_count ?? 12;
                $intervenant->missions_completees = $intervenant->interventions_count ?? 0;
                // Expose pivot price at top level
                $intervenant->tarif_tache = $intervenant->pivot->prix_tache ?? null;
                
                return $intervenant;
            });
            
            return response()->json([
                'intervenants' => $intervenants,
                'task_id' => $id,
                'count' => $intervenants->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error in getIntervenants: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'details' => 'Check the logs for more information'
            ], 500);
        }
    }
}
