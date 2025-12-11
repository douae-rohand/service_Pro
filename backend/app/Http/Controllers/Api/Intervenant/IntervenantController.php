<?php

namespace App\Http\Controllers\Api\Intervenant;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use App\Models\Materiel;
use Illuminate\Http\Request;

class IntervenantController extends Controller
{
    /**
     * Display a listing of intervenants
     */
    public function index(Request $request)
    {
        $query = Intervenant::with(['utilisateur', 'admin.utilisateur', 'taches']);

        // Filtrer les intervenants actifs uniquement si demandé
        if ($request->has('active') && $request->active == 'true') {
            $query->active();
        }

        // Filtrer par tâche spécifique (intervenants pouvant effectuer une tâche)
        if ($request->has('tacheId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('tache.id', $request->tacheId);
            });
        }

        $intervenants = $query->get();

        return response()->json($intervenants);
    }

    /**
     * Store a newly created intervenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:utilisateur,id',
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $intervenant = Intervenant::create($validated);

        return response()->json([
            'message' => 'Intervenant créé avec succès',
            'intervenant' => $intervenant->load('utilisateur'),
        ], 201);
    }

    /**
     * Display the specified intervenant
     */
    public function show($id)
    {
        $intervenant = Intervenant::with([
            'utilisateur',
            'admin.utilisateur',
            'interventions',
            'disponibilites',
            'taches',
            'materiels',
            'clientsFavoris.utilisateur'
        ])->findOrFail($id);

        return response()->json($intervenant);
    }

    /**
     * Update the specified intervenant
     */
    public function update(Request $request, $id)
    {
        $intervenant = Intervenant::findOrFail($id);

        $validated = $request->validate([
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $intervenant->update($validated);

        return response()->json([
            'message' => 'Intervenant mis à jour avec succès',
            'intervenant' => $intervenant->load('utilisateur'),
        ]);
    }

    /**
     * Remove the specified intervenant
     */
    public function destroy($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $intervenant->delete();

        return response()->json([
            'message' => 'Intervenant supprimé avec succès',
        ]);
    }

    /**
     * Get interventions for a specific intervenant
     */
    public function interventions($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $interventions = $intervenant->interventions()
            ->with(['client.utilisateur', 'tache'])
            ->orderBy('dateIntervention', 'desc')
            ->get();

        return response()->json($interventions);
    }

    /**
     * Get disponibilites for a specific intervenant
     */
    public function disponibilites($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $disponibilites = $intervenant->disponibilites()
            ->orderBy('dateDebut', 'asc')
            ->get();

        return response()->json($disponibilites);
    }

    /**
     * Get taches that this intervenant can perform
     */
    public function taches($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $taches = $intervenant->taches()
            ->with('service')
            ->get();

        return response()->json($taches);
    }

    /**
     * Get current intervenant's taches with all details
     */
    public function myTaches(Request $request)
    {
        // TODO: Uncomment when authentication is implemented
        // $user = $request->user();
        // 
        // if (!$user->intervenant) {
        //     return response()->json([
        //         'message' => 'Utilisateur non associé à un intervenant'
        //     ], 403);
        // }
        // 
        // $intervenant = $user->intervenant;
        
        // TEMPORARY: Hardcoded intervenant ID for testing (from seeders: intervenant id=5)
        $intervenantId = 5;
        $intervenant = Intervenant::findOrFail($intervenantId);
        
        // Get taches with pivot data, service, materials, and completed jobs count
        $taches = $intervenant->taches()
            ->with(['service', 'materiels'])
            ->get()
            ->map(function ($tache) use ($intervenant) {
                // Get pivot data (tarif, experience, archived, active)
                $pivot = $tache->pivot;
                
                // Count completed interventions for this tache
                $completedJobs = $intervenant->interventions()
                    ->where('tache_id', $tache->id)
                    ->where('status', 'terminée')
                    ->count();
                
                // Get materials names
                $materials = $tache->materiels->pluck('nom_materiel')->toArray();
                
                // Determine service type (menage or jardinage) from service name
                $serviceType = 'menage'; // default
                if ($tache->service) {
                    $serviceName = strtolower($tache->service->nom_service ?? '');
                    if (strpos($serviceName, 'jardin') !== false) {
                        $serviceType = 'jardinage';
                    }
                }
                
                return [
                    'id' => $tache->id,
                    'type' => $serviceType,
                    'name' => $tache->nom_tache,
                    'description' => $tache->description,
                    'hourlyRate' => (float) ($pivot->prix_tache ?? 0),
                    'active' => $pivot->status ?? true,
                    'completedJobs' => $completedJobs,
                    'materials' => $materials,
                ];
            });

        return response()->json($taches);
    }

    /**
     * Update current intervenant's tache
     */
    public function updateMyTache(Request $request, $tacheId)
    {
        // TODO: Uncomment when authentication is implemented
        // $user = $request->user();
        // 
        // if (!$user->intervenant) {
        //     return response()->json([
        //         'message' => 'Utilisateur non associé à un intervenant'
        //     ], 403);
        // }
        // 
        // $intervenant = $user->intervenant;
        
        // TEMPORARY: Hardcoded intervenant ID for testing (from seeders: intervenant id=5)
        $intervenantId = 5;
        $intervenant = Intervenant::findOrFail($intervenantId);
        
        // Check if the intervenant has this tache
        $tache = $intervenant->taches()->find($tacheId);
        if (!$tache) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $validated = $request->validate([
            'description' => 'sometimes|string',
            'hourlyRate' => 'sometimes|numeric|min:0',
            'materials' => 'sometimes|array',
            'materials.*' => 'string', // Material names as strings
            'active' => 'sometimes|boolean',
        ]);

        // Update pivot data
        $pivotData = [];
        if (isset($validated['hourlyRate'])) {
            $pivotData['prix_tache'] = $validated['hourlyRate'];
        }
        if (isset($validated['active'])) {
            $pivotData['status'] = $validated['active'];
        }
        
        if (!empty($pivotData)) {
            $intervenant->taches()->updateExistingPivot($tacheId, $pivotData);
        }

        // Update tache description if provided
        if (isset($validated['description'])) {
            $tache->update(['description' => $validated['description']]);
        }

        // Update materials if provided - convert names to IDs with fuzzy matching
        if (isset($validated['materials'])) {
            $materialIds = [];
            $allMaterials = Materiel::pluck('nom_materiel', 'id')->toArray();
            
            foreach ($validated['materials'] as $materialName) {
                // Try exact match first
                $material = Materiel::where('nom_materiel', $materialName)->first();
                
                if ($material) {
                    $materialIds[] = $material->id;
                } else {
                    // Try partial/contains match for variations
                    $matchedMaterial = Materiel::where('nom_materiel', 'like', '%' . $materialName . '%')->first();
                    if ($matchedMaterial) {
                        $materialIds[] = $matchedMaterial->id;
                        \Log::info("Partial match found: '{$materialName}' → '{$matchedMaterial->nom_materiel}'");
                    } else {
                        \Log::warning("Material not found: '{$materialName}'. Available materials: " . json_encode(array_values($allMaterials)));
                    }
                }
            }
            
            // Debug logging
            \Log::info("Syncing materials for tache {$tacheId}: " . json_encode($materialIds));
            
            $tache->materiels()->sync($materialIds);
            
            // Verify the sync worked
            $syncedMaterials = $tache->materiels()->get()->pluck('nom_materiel')->toArray();
            \Log::info("Materials after sync: " . json_encode($syncedMaterials));
        }

        return response()->json([
            'message' => 'Tâche mise à jour avec succès',
            'updatedMaterials' => isset($validated['materials']) ? $tache->materiels()->get()->pluck('nom_materiel')->toArray() : null,
        ]);
    }

    /**
     * Toggle active status of current intervenant's tache
     */
    public function toggleActiveMyTache(Request $request, $tacheId)
    {
        // TODO: Uncomment when authentication is implemented
        // $user = $request->user();
        // 
        // if (!$user->intervenant) {
        //     return response()->json([
        //         'message' => 'Utilisateur non associé à un intervenant'
        //     ], 403);
        // }
        // 
        // $intervenant = $user->intervenant;
        
        // TEMPORARY: Hardcoded intervenant ID for testing (from seeders: intervenant id=5)
        $intervenantId = 5;
        $intervenant = Intervenant::findOrFail($intervenantId);
        
        $tache = $intervenant->taches()->find($tacheId);
        if (!$tache) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $currentStatus = $tache->pivot->status ?? true;
        
        $intervenant->taches()->updateExistingPivot($tacheId, [
            'status' => !$currentStatus,
        ]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'active' => !$currentStatus,
        ]);
    }

    /**
     * Delete current intervenant's tache (remove relationship)
     */
    public function deleteMyTache(Request $request, $tacheId)
    {
        // TODO: Uncomment when authentication is implemented
        // $user = $request->user();
        // 
        // if (!$user->intervenant) {
        //     return response()->json([
        //         'message' => 'Utilisateur non associé à un intervenant'
        //     ], 403);
        // }
        // 
        // $intervenant = $user->intervenant;
        
        // TEMPORARY: Hardcoded intervenant ID for testing (from seeders: intervenant id=5)
        $intervenantId = 5;
        $intervenant = Intervenant::findOrFail($intervenantId);
        
        $tache = $intervenant->taches()->find($tacheId);
        if (!$tache) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $intervenant->taches()->detach($tacheId);

        return response()->json([
            'message' => 'Tâche supprimée avec succès',
        ]);
    }

    /**
     * Get the active services and tasks for an intervenant
     */
    public function getActiveServicesAndTasks($intervenantId)
    {
        $intervenant = Intervenant::with(['services', 'taches'])->find($intervenantId);

        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Get active services
        $activeServices = $intervenant->services()
            ->wherePivot('status', 'active')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->nom_service,
                    'status' => $service->pivot->status,
                ];
            });

        // Get active tasks
        $activeTasks = $intervenant->taches()
            ->wherePivot('status', 1)  // status = 1 means active
            ->get()
            ->map(function ($tache) {
                return [
                    'id' => $tache->id,
                    'name' => $tache->nom_tache,
                    'price' => $tache->pivot->prix_tache,
                    'status' => $tache->pivot->status,
                ];
            });

        return response()->json([
            'services' => $activeServices,
            'tasks' => $activeTasks,
        ]);
    }
}
