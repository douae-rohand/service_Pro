<?php

namespace App\Http\Controllers\Api\Intervenant;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use App\Models\Service;
use App\Models\Materiel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntervenantController extends Controller
{
    /**
     * Display a listing of intervenants
     */
    public function index(Request $request)
    {
        // Optimiser le chargement : ne charger que les relations nécessaires
        $query = Intervenant::with([
            'utilisateur:id,nom,prenom,address',
            'taches:id,nom_tache,service_id',
            'taches.service:id,nom_service'
        ]);

        // Filtrer les intervenants actifs uniquement si demandé
        if ($request->has('active') && $request->active == 'true') {
            $query->active();
        }

        // Filtrer par tâche spécifique (intervenants pouvant effectuer une tâche)
        if ($request->has('tacheId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('id', $request->tacheId);
            });
        }

        // Filtrer par service si spécifié - optimisé
        if ($request->has('serviceId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('service_id', $request->serviceId);
            });
        }

        // Sélectionner uniquement les colonnes nécessaires
        $intervenants = $query->select('intervenant.id', 'intervenant.ville', 'intervenant.bio', 'intervenant.is_active')
            ->get();

        // Calculer la note moyenne et le nombre d'avis pour chaque intervenant
        foreach ($intervenants as $intervenant) {
            $ratingInfo = $intervenant->getRatingInfo();
            $intervenant->average_rating = $ratingInfo['average_rating'];
            $intervenant->review_count = $ratingInfo['review_count'];
        }

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

        // Calculer la note moyenne et le nombre d'avis
        $ratingInfo = $intervenant->getRatingInfo();
        $intervenant->average_rating = $ratingInfo['average_rating'];
        $intervenant->review_count = $ratingInfo['review_count'];

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
        // Get authenticated user
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        if (!$user->intervenant) {
            return response()->json([
                'message' => 'Utilisateur non associé à un intervenant',
                'user_id' => $user->id,
                'user_email' => $user->email
            ], 403);
        }
        
        $intervenant = $user->intervenant;

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
    // Get authenticated user
    $user = $request->user();
    
    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur non authentifié'
        ], 401);
    }
    
    if (!$user->intervenant) {
        return response()->json([
            'message' => 'Utilisateur non associé à un intervenant',
            'user_id' => $user->id,
            'user_email' => $user->email
        ], 403);
    }
    
    $intervenant = $user->intervenant;

    // Check if the intervenant has this tache, if not create the relationship
    $tache = $intervenant->taches()->find($tacheId);
    
    if (!$tache) {
        // Get the task to make sure it exists
        $taskExists = \App\Models\Tache::find($tacheId);
        if (!$taskExists) {
            return response()->json([
                'message' => 'Tâche introuvable dans la base de données',
                'tacheId' => $tacheId
            ], 404);
        }
        
        // Create the relationship with default values
        $intervenant->taches()->attach($tacheId, [
            'prix_tache' => $validated['hourlyRate'] ?? 0,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Get the newly attached task
        $tache = $intervenant->taches()->find($tacheId);
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

    // Update materials if provided - store in intervenant_materiel table
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

        // Sync materials for this intervenant (not for the task)
        $intervenant->materiels()->sync($materialIds);
        
        \Log::info("Syncing materials for intervenant {$intervenant->id}: " . json_encode($materialIds));
    }

    return response()->json([
        'message' => 'Tâche mise à jour avec succès',
        'hourlyRate' => $validated['hourlyRate'] ?? null,
        'updatedMaterials' => isset($validated['materials']) ? $intervenant->materiels()->get()->pluck('nom_materiel')->toArray() : null,
    ]);
}

    /**
     * Configure task with price and materials
     */
    public function configureTask(Request $request, $intervenantId, $tacheId)
    {
        // Verify intervenant exists
        $intervenant = Intervenant::find($intervenantId);
        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Check if the intervenant has this tache
        $tache = $intervenant->taches()->find($tacheId);
        if (!$tache) {
            return response()->json([
                'message' => 'Tâche non trouvée pour cet intervenant'
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'hourlyRate' => 'required|numeric|min:0',
            'materials' => 'sometimes|array',
            'materials.*' => 'string',
        ]);

        // Update task price in pivot table
        $intervenant->taches()->updateExistingPivot($tacheId, [
            'prix_tache' => $validated['hourlyRate'],
        ]);

        // Update materials if provided
        if (isset($validated['materials']) && !empty($validated['materials'])) {
            $materialIds = [];

            foreach ($validated['materials'] as $materialName) {
                // Try to find material by name
                $material = Materiel::where('nom_materiel', $materialName)->first();

                if ($material) {
                    $materialIds[] = $material->id;
                }
            }

            // Sync materials for this task
            $tache->materiels()->sync($materialIds);
        } else {
            // If no materials, remove all
            $tache->materiels()->sync([]);
        }

        return response()->json([
            'message' => 'Configuration enregistrée avec succès',
            'hourlyRate' => $validated['hourlyRate'],
        ]);
    }

    /**
     * Toggle active status for current intervenant's tache
     */
    public function toggleActiveMyTache(Request $request, $tacheId)
    {
        // Get authenticated user
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        if (!$user->intervenant) {
            return response()->json([
                'message' => 'Utilisateur non associé à un intervenant',
                'user_id' => $user->id,
                'user_email' => $user->email
            ], 403);
        }
        
        $intervenant = $user->intervenant;

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
        // Get authenticated user
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        if (!$user->intervenant) {
            return response()->json([
                'message' => 'Utilisateur non associé à un intervenant',
                'user_id' => $user->id,
                'user_email' => $user->email
            ], 403);
        }
        
        $intervenant = $user->intervenant;

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

        // Get all tasks (active and inactive) with their status
        $allTasks = $intervenant->taches()
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
            'tasks' => $allTasks,
        ]);
    }

    /**
     * Get all services with activation status for current intervenant
     */
    public function getServicesWithActivation($intervenantId)
    {
        // Verify intervenant exists
        $intervenant = Intervenant::find($intervenantId);
        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Get all services with their tasks and materials
        $allServices = Service::with(['taches.materiels'])->get();

        // Get intervenant's active services
        $activeServices = DB::table('intervenant_service')
            ->where('intervenant_id', $intervenantId)
            ->where('status', 'active')
            ->pluck('service_id')
            ->toArray();

        // Get intervenant's active tasks
        $activeTasks = DB::table('intervenant_tache')
            ->where('intervenant_id', $intervenantId)
            ->where('active', true)
            ->pluck('tache_id')
            ->toArray();

        // Map services with their activation status
        $servicesData = $allServices->map(function ($service) use ($activeServices, $activeTasks) {
            return [
                'id' => $service->id,
                'name' => $service->nom_service,
                'description' => $service->description,
                'isActive' => in_array($service->id, $activeServices),
                'tasks' => $service->taches->map(function ($task) use ($activeTasks) {
                    return [
                        'id' => $task->id,
                        'name' => $task->nom_tache,
                        'description' => $task->description,
                        'isActive' => in_array($task->id, $activeTasks),
                        'materials' => $task->materiels->map(function ($material) {
                            return [
                                'id' => $material->id,
                                'name' => $material->nom_materiel,
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json(['services' => $servicesData]);
    }

    /**
     * Toggle service activation
     */
    public function toggleService($intervenantId, $serviceId)
    {
        // Verify intervenant exists
        $intervenant = Intervenant::find($intervenantId);
        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Verify service exists
        $service = Service::find($serviceId);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        // Check if relation exists
        $existing = DB::table('intervenant_service')
            ->where('intervenant_id', $intervenantId)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            // Toggle status
            $newStatus = ($existing->status === 'active') ? 'desactive' : 'active';

            DB::table('intervenant_service')
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => 'Statut mis à jour',
                'isActive' => $newStatus === 'active'
            ]);
        } else {
            // Create new active relation
            DB::table('intervenant_service')->insert([
                'intervenant_id' => $intervenantId,
                'service_id' => $serviceId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Service activé',
                'isActive' => true
            ]);
        }
    }
}
