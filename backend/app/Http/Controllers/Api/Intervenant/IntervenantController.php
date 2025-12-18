<?php

namespace App\Http\Controllers\Api\Intervenant;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use App\Models\Intervention;
use App\Models\Service;
use App\Models\Materiel;
use App\Models\Disponibilite;
use App\Models\Tache;
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
            'taches.service:id,nom_service',
            'services'
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

        // Optimisation : Eager loading des interventions et leurs évaluations pour éviter le N+1
        $query->with(['interventions.evaluations' => function ($q) {
            $q->where('type_auteur', 'client');
        }]);

        // Sélectionner uniquement les colonnes nécessaires
        $intervenants = $query->select('intervenant.id', 'intervenant.ville', 'intervenant.bio', 'intervenant.is_active')
            ->get();

        // Calculer la note moyenne et le nombre d'avis en mémoire pour éviter les requêtes SQL en boucle
        foreach ($intervenants as $intervenant) {
            // Récupérer toutes les notes des évaluations clients de cet intervenant via ses interventions
            // On utilise flatMap pour récupérer toutes les évaluations de toutes les interventions
            $evaluations = $intervenant->interventions->flatMap(function ($intervention) {
                return $intervention->evaluations;
            });
            
            $count = $evaluations->count();
            $avg = $count > 0 ? $evaluations->avg('note') : 0;
            
            $intervenant->average_rating = round($avg, 1);
            $intervenant->review_count = $count;
            
            // Nettoyer la relation pour ne pas renvoyer de données inutiles en JSON
            unset($intervenant->interventions);
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
            'is_active' => 'nullable|boolean',
            'admin_id' => 'nullable|exists:admin,id',
        ]);

        // Support legacy camelCase payloads
        $intervenant = Intervenant::create([
            'id' => $validated['id'],
            'address' => $validated['address'] ?? $request->input('address'),
            'ville' => $validated['ville'] ?? $request->input('ville'),
            'bio' => $validated['bio'] ?? $request->input('bio'),
            'is_active' => $validated['is_active'] ?? $request->boolean('isActive'),
            'admin_id' => $validated['admin_id'] ?? $request->input('adminId'),
        ]);

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
                'utilisateur:id,nom,prenom,address',
                'taches:id,nom_tache,service_id',
                'taches.service:id,nom_service',
                'services',
                'interventions' => function($q) {
                    $q->orderBy('date_intervention', 'desc');
                },
                'interventions.photos',
                'interventions.evaluations' => function($q) {
                    $q->where('type_auteur', 'client');
                },
                'interventions.commentaires' => function($q) {
                    $q->where('type_auteur', 'client');
                },
                'interventions.client.utilisateur:id,nom,prenom',
                'disponibilites'
            ])->find($id);

            if (!$intervenant) {
                return response()->json([
                    'message' => 'Intervenant introuvable'
                ], 404);
            }

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
            'is_active' => 'nullable|boolean',
            'admin_id' => 'nullable|exists:admin,id',
        ]);

        $intervenant->update([
            'address' => $validated['address'] ?? $request->input('address'),
            'ville' => $validated['ville'] ?? $request->input('ville'),
            'bio' => $validated['bio'] ?? $request->input('bio'),
            'is_active' => $validated['is_active'] ?? $request->boolean('isActive'),
            'admin_id' => $validated['admin_id'] ?? $request->input('adminId'),
        ]);

        // Update service experience if provided
        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['id']) && isset($serviceData['experience'])) {
                    // Update existing pivot
                    $intervenant->services()->updateExistingPivot(
                        $serviceData['id'], 
                        ['experience' => $serviceData['experience']]
                    );
                }
            }
        }

        return response()->json([
            'message' => 'Intervenant mis à jour avec succès',
            'intervenant' => $intervenant->load('utilisateur', 'services'), // Reload services to return updated data
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
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($disponibilites);
    }

    /**
     * Get current authenticated intervenant's disponibilites
     */
    public function myDisponibilites(Request $request)
    {
        $user = $request->user();
        $intervenant = $user->intervenant;

        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        $disponibilites = $intervenant->disponibilites()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($disponibilites);
    }

    /**
     * Create regular availability (weekly schedule)
     */
    public function createRegularAvailability(Request $request)
    {
        $user = $request->user();
        $intervenant = $user->intervenant;

        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        $validated = $request->validate([
            'availabilities' => 'required|array',
            'availabilities.*.day' => 'required|string|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'availabilities.*.available' => 'required|boolean',
            'availabilities.*.startTime' => 'nullable|string',
            'availabilities.*.endTime' => 'nullable|string',
        ]);

        // Delete existing regular availabilities for this intervenant
        Disponibilite::where('intervenant_id', $intervenant->id)
            ->where('type', 'reguliere')
            ->delete();

        // Create new regular availabilities
        $createdDisponibilites = [];
        foreach ($validated['availabilities'] as $availability) {
            if ($availability['available']) {
                $disponibilite = Disponibilite::create([
                    'type' => 'reguliere',
                    'jours_semaine' => $availability['day'],
                    'heure_debut' => $availability['startTime'],
                    'heure_fin' => $availability['endTime'],
                    'intervenant_id' => $intervenant->id,
                ]);
                $createdDisponibilites[] = $disponibilite;
            }
        }

        return response()->json([
            'message' => 'Disponibilités régulières créées avec succès',
            'disponibilites' => $createdDisponibilites
        ]);
    }

    /**
     * Create special availability (one-time exception)
     */
    public function createSpecialAvailability(Request $request)
    {
        $user = $request->user();
        $intervenant = $user->intervenant;

        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'available' => 'required|boolean',
            'startTime' => 'nullable|string',
            'endTime' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $disponibilite = Disponibilite::create([
            'type' => 'ponctuelle',
            'date_specific' => $validated['date'],
            'heure_debut' => $validated['available'] ? $validated['startTime'] : null,
            'heure_fin' => $validated['available'] ? $validated['endTime'] : null,
            'intervenant_id' => $intervenant->id,
        ]);

        return response()->json([
            'message' => 'Disponibilité ponctuelle créée avec succès',
            'disponibilite' => $disponibilite
        ]);
    }

    /**
     * Delete a specific disponibilite
     */
    public function deleteDisponibilite(Request $request, $id)
    {
        $user = $request->user();
        $intervenant = $user->intervenant;

        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        $disponibilite = Disponibilite::where('id', $id)
            ->where('intervenant_id', $intervenant->id)
            ->first();

        if (!$disponibilite) {
            return response()->json(['error' => 'Disponibilité non trouvée'], 404);
        }

        $disponibilite->delete();

        return response()->json(['message' => 'Disponibilité supprimée avec succès']);
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

                // Get all materials for this task
                $taskMaterials = $tache->materiels;
                
                // Get IDs of materials owned by the intervenant
                $intervenantMaterialIds = $intervenant->materiels->pluck('id')->toArray();

                // Filter task materials to include only those owned by the intervenant
                $ownedMaterials = $taskMaterials->filter(function ($material) use ($intervenantMaterialIds) {
                    return in_array($material->id, $intervenantMaterialIds);
                })->pluck('nom_materiel')->toArray();

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
                    'materials' => $ownedMaterials,
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

    // Validate request first before using any validated data
    $validated = $request->validate([
        'description' => 'sometimes|string',
        'hourlyRate' => 'sometimes|numeric|min:0',
        'materials' => 'sometimes|array',
        'materials.*.name' => 'required|string',
        'materials.*.price' => 'required|numeric|min:0',
        'active' => 'sometimes|boolean',
    ]);

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

    // Update pivot data
    $pivotData = [];
    if (isset($validated['hourlyRate'])) {
        $pivotData['prix_tache'] = $validated['hourlyRate'];
        // When updating price, also set status to active (1)
        $pivotData['status'] = 1;
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

    // Update materials if provided - store in intervenant_materiel table with prices
    if (isset($validated['materials'])) {
        $materialIds = [];
        $materialSyncData = [];
        $allMaterials = Materiel::pluck('nom_materiel', 'id')->toArray();

        foreach ($validated['materials'] as $materialData) {
            $materialName = $materialData['name'];
            $materialPrice = $materialData['price'];

            // Try exact match first
            $material = Materiel::where('nom_materiel', $materialName)->first();

            if ($material) {
                $materialIds[] = $material->id;
                $materialSyncData[$material->id] = ['prix_materiel' => $materialPrice];
            } else {
                // Try partial/contains match for variations
                $matchedMaterial = Materiel::where('nom_materiel', 'like', '%' . $materialName . '%')->first();
                if ($matchedMaterial) {
                    $materialIds[] = $matchedMaterial->id;
                    $materialSyncData[$matchedMaterial->id] = ['prix_materiel' => $materialPrice];
                    Log::info("Partial match found: '{$materialName}' → '{$matchedMaterial->nom_materiel}'");
                } else {
                    Log::warning("Material not found: '{$materialName}'. Available materials: " . json_encode(array_values($allMaterials)));
                }
            }
        }

        
        // Get all materials that are relevant to this task (offered as options)
        $taskMaterials = $tache->materiels->pluck('id')->toArray();
        
        // Identify materials that were unchecked (in Task but not in Request)
        // These should be removed from the intervenant's inventory
        $materialsToDetach = array_diff($taskMaterials, $materialIds);
        
        if (!empty($materialsToDetach)) {
             $intervenant->materiels()->detach($materialsToDetach);
        }

        // Sync materials for this intervenant with prices (without detaching others)
        // This updates existing pivots and attaches new ones
        $intervenant->materiels()->sync($materialSyncData, false);

        Log::info("Updated materials for intervenant {$intervenant->id} via task {$tacheId}");
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

        // Update task price and status in pivot table
        $intervenant->taches()->updateExistingPivot($tacheId, [
            'prix_tache' => $validated['hourlyRate'],
            'status' => 1, // Set status to active (1) when configuring for the first time
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
                'prix_tache' => 0,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Get the newly attached task
            $tache = $intervenant->taches()->find($tacheId);
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

        // Get IDs of materials owned by the intervenant
        $intervenantMaterialIds = $intervenant->materiels->pluck('id')->toArray();

        // Get all tasks (active and inactive) with their status
        $allTasks = $intervenant->taches()
            ->with('materiels')
            ->get()
            ->map(function ($tache) use ($intervenantMaterialIds) {
                // Filter task materials to include only those owned by the intervenant
                $ownedMaterials = $tache->materiels->filter(function ($material) use ($intervenantMaterialIds) {
                    return in_array($material->id, $intervenantMaterialIds);
                })->pluck('nom_materiel')->toArray();

                return [
                    'id' => $tache->id,
                    'name' => $tache->nom_tache,
                    'price' => $tache->pivot->prix_tache,
                    'status' => $tache->pivot->status,
                    'materials' => $ownedMaterials,
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
     * Request service activation with documents
     */
    public function requestActivation(Request $request, $intervenantId, $serviceId)
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

        // Validate request
        $validated = $request->validate([
            'presentation' => 'required|string',
            'experience' => 'required|numeric|min:0',
            'documents' => 'required|array|min:2', // idCard and insurance required
            'documents.*.type' => 'required|string',
            'documents.*.file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Check if relation already exists
            $existing = DB::table('intervenant_service')
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->first();

            if ($existing && $existing->status !== 'refuse' && $existing->status !== 'desactive') {
                return response()->json([
                    'message' => 'Une demande existe déjà pour ce service',
                    'status' => $existing->status
                ], 400);
            }

            // Validate required documents
            $hasIdCard = false;
            $hasInsurance = false;

            foreach ($validated['documents'] as $doc) {
                if ($doc['type'] === 'idCard') $hasIdCard = true;
                if ($doc['type'] === 'insurance') $hasInsurance = true;
            }

            if (!$hasIdCard || !$hasInsurance) {
                return response()->json([
                    'message' => 'La carte d\'identité et l\'assurance sont obligatoires'
                ], 400);
            }

            DB::beginTransaction();

            // Create or update relation with 'demmande' status
            if ($existing) {
                // Update existing refused request
                DB::table('intervenant_service')
                    ->where('intervenant_id', $intervenantId)
                    ->where('service_id', $serviceId)
                    ->update([
                        'status' => 'demmande',
                        'presentation' => $validated['presentation'],
                        'experience' => $validated['experience'],
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new relation
                DB::table('intervenant_service')->insert([
                    'intervenant_id' => $intervenantId,
                    'service_id' => $serviceId,
                    'status' => 'demmande',
                    'presentation' => $validated['presentation'],
                    'experience' => $validated['experience'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Store documents in justificatif table and link to service
            foreach ($validated['documents'] as $doc) {
                // Store file
                $filePath = $doc['file']->store('justificatifs', 'public');

                // Create justificatif record
                $justificatifId = DB::table('justificatif')->insertGetId([
                    'type' => $doc['type'],
                    'chemin_fichier' => $filePath,
                    'intervenant_id' => $intervenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link justificatif to service
                DB::table('service_justificatif')->insert([
                    'justificatif_id' => $justificatifId,
                    'service_id' => $serviceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Demande d\'activation envoyée avec succès',
                'status' => 'demmande',
                'isActive' => false
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in requestActivation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du traitement de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a material from intervenant_materiel table
     */
    public function deleteIntervenantMaterial($intervenantId, $materialId)
    {
        // Verify intervenant exists
        $intervenant = Intervenant::find($intervenantId);
        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Delete the material association
        $deleted = DB::table('intervenant_materiel')
            ->where('intervenant_id', $intervenantId)
            ->where('materiel_id', $materialId)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Material removed successfully']);
        } else {
            return response()->json(['error' => 'Material not found for this intervenant'], 404);
        }
    }

    /**
     * Get intervenant materials for a specific service
     */
    public function getIntervenantMaterials($intervenantId, $serviceId)
    {
        // Verify intervenant exists
        $intervenant = Intervenant::find($intervenantId);
        if (!$intervenant) {
            return response()->json(['error' => 'Intervenant not found'], 404);
        }

        // Get materials for this service with intervenant prices
        $materials = DB::table('materiel')
            ->leftJoin('intervenant_materiel', function ($join) use ($intervenantId) {
                $join->on('materiel.id', '=', 'intervenant_materiel.materiel_id')
                     ->where('intervenant_materiel.intervenant_id', '=', $intervenantId);
            })
            ->where('materiel.service_id', '=', $serviceId)
            ->select([
                'materiel.id as material_id',
                'materiel.nom_materiel as material_name',
                'intervenant_materiel.prix_materiel'
            ])
            ->get();

        // Format materials with possession status and price
        $formattedMaterials = [];
        foreach ($materials as $material) {
            $formattedMaterials[] = [
                'id' => $material->material_id,
                'name' => $material->material_name,
                'price' => $material->prix_materiel ?: 0,
                'possessed' => $material->prix_materiel !== null // Material is possessed if price exists
            ];
        }

        return response()->json([
            'materials' => $formattedMaterials
        ]);
    }

    /**
     * Update service materials
     */
    public function updateServiceMaterials(Request $request, $intervenantId, $serviceId)
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

        // Validate request
        $validated = $request->validate([
            'materials' => 'sometimes|array', // Allow empty array
            'materials.*.name' => 'required|string',
            'materials.*.price' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Process each material (only if materials array is not empty)
        if (!empty($validated['materials'])) {
            foreach ($validated['materials'] as $materialData) {
                $materialName = $materialData['name'];
                $materialPrice = $materialData['price'];

                // Find the material
                $material = Materiel::where('nom_materiel', $materialName)->first();
                if (!$material) {
                    return response()->json([
                        'error' => "Material '{$materialName}' not found"
                    ], 404);
                }

                // Store or update price in intervenant_materiel table
                DB::table('intervenant_materiel')
                    ->updateOrInsert(
                        [
                            'intervenant_id' => $intervenantId,
                            'materiel_id' => $material->id
                        ],
                        [
                            'prix_materiel' => $materialPrice,
                            'updated_at' => now()
                        ]
                    );
            }
        }

            DB::commit();

            return response()->json([
                'message' => 'Matériaux enregistrés avec succès',
                'materials_count' => count($validated['materials'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in updateServiceMaterials: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement des matériaux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update service status
     */
    public function updateServiceStatus(Request $request, $intervenantId, $serviceId)
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

        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:active,desactive,archive'
        ]);

        // Check if relation exists
        $existing = DB::table('intervenant_service')
            ->where('intervenant_id', $intervenantId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$existing) {
            return response()->json(['error' => 'Service relation not found'], 404);
        }

        // Update status
        DB::table('intervenant_service')
            ->where('intervenant_id', $intervenantId)
            ->where('service_id', $serviceId)
            ->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Statut mis à jour',
            'status' => $validated['status']
        ]);
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

            // If deactivating, clear presentation and experience to allow new requests
            $updateData = [
                'status' => $newStatus,
                'updated_at' => now(),
            ];

            if ($newStatus === 'desactive') {
                $updateData['presentation'] = null;
                $updateData['experience'] = null;
            }

            DB::table('intervenant_service')
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->update($updateData);

            return response()->json([
                'message' => 'Statut mis à jour',
                'isActive' => $newStatus === 'active'
            ]);
        } else {
            // Create new relation with 'demmande' status (pending approval)
            DB::table('intervenant_service')->insert([
                'intervenant_id' => $intervenantId,
                'service_id' => $serviceId,
                'status' => 'demmande',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Demande d\'activation envoyée',
                'status' => 'demmande',
                'isActive' => false
            ]);
        }
    }

     /**
     * select current intervenant's Reservation
     */
    public function myReservations(Request $request)
    {
        // Get the current authenticated intervenant
        $intervenant = $request->user();

        if (!$intervenant) {
            return response()->json(['message' => 'Intervenant non authentifié'], 401);
        }

        // Get all interventions for this intervenant with relationships and materials
        $interventions = Intervention::with([
            'client.utilisateur',
            'tache.service',
            'tache.materiels',
            'materiels' // Materials used in this specific intervention
        ])
        ->where('intervenant_id', $intervenant->id)
        ->orderBy('date_intervention', 'desc')
        ->get();

        // Format the data for frontend
        $formattedInterventions = $interventions->map(function ($intervention) {
            $client = $intervention->client;
            $clientUser = $client ? $client->utilisateur : null;
            $tache = $intervention->tache;

            // Get materials provided by client (from tache materiels)
            $clientProvidedMaterials = [];
            if ($tache && $tache->materiels) {
                $clientProvidedMaterials = $tache->materiels->map(function ($materiel) {
                    return [
                        'id' => $materiel->id,
                        'name' => $materiel->nom_materiel,
                        'description' => $materiel->description,
                        'provided_by' => 'client'
                    ];
                });
            }

            // Get materials used by intervenant in this intervention
            $intervenantMaterials = [];
            if ($intervention->materiels) {
                $intervenantMaterials = $intervention->materiels->map(function ($materiel) {
                    return [
                        'id' => $materiel->id,
                        'name' => $materiel->nom_materiel,
                        'description' => $materiel->description,
                        'provided_by' => 'intervenant'
                    ];
                });
            }

            // Combine all materials
            $allMaterials = $clientProvidedMaterials->merge($intervenantMaterials);

            return [
                'id' => $intervention->id,
                'clientName' => $clientUser ? ($clientUser->nom . ' ' . $clientUser->prenom) : 'Client inconnu',
                'clientImage' => $clientUser->photo ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop',
                'service' => $tache && $tache->service ? $tache->service->nom_service : 'Service inconnu',
                'task' => $tache ? $tache->nom_tache : 'Tâche inconnue',
                'date' => date('Y-m-d', strtotime($intervention->date_intervention)),
                'time' => date('H:i', strtotime($intervention->date_intervention)),
                'duration' => '2 heures', // You might want to add this field to the intervention table
                'hourlyRate' => 25, // You might want to calculate this from the tache pivot table
                'location' => $intervention->address . ', ' . $intervention->ville,
                'status' => $this->mapStatus($intervention->status),
                'message' => null, // You might want to add a message field
                'materials' => $allMaterials,
                'clientProvidedMaterials' => $clientProvidedMaterials,
                'intervenantMaterials' => $intervenantMaterials
            ];
        });

        // Calculate intervenant-specific statistics
        $pendingCount = $interventions->where('status', 'en attend')->count();
        $acceptedCount = $interventions->where('status', 'acceptee')->count();
        $completedCount = $interventions->where('status', 'termine')->count();
        $totalEarnings = $interventions
            ->where('status', 'termine')
            ->sum(function ($intervention) {
                // Calculate based on duration and hourly rate (using default 2 hours and 25 DH/h)
                return 2 * 25; // duration * hourlyRate
            });

        return response()->json([
            'reservations' => $formattedInterventions,
            'statistics' => [
                'pending' => $pendingCount,
                'accepted' => $acceptedCount,
                'completed' => $completedCount,
                'total_earnings' => $totalEarnings,
                'total_interventions' => $interventions->count(),
                'completion_rate' => $interventions->count() > 0
                    ? round(($completedCount / $interventions->count()) * 100, 1)
                    : 0
            ]
        ]);
    }

    /**
     * Map database status to frontend status
     */
    private function mapStatus($status)
    {
        $statusMap = [
            'en attend' => 'pending',
            'acceptee' => 'accepted',
            'termine' => 'completed',
            'refuse' => 'refused',
            'annulee' => 'cancelled'
        ];

        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Accept a reservation
     */
    public function acceptReservation(Request $request, $id)
    {
        $intervenant = $request->user();

        if (!$intervenant) {
            return response()->json(['message' => 'Intervenant non authentifié'], 401);
        }

        $intervention = Intervention::where('id', $id)
            ->where('intervenant_id', $intervenant->id)
            ->first();

        if (!$intervention) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }

        $intervention->status = 'acceptee';
        $intervention->save();

        return response()->json(['message' => 'Réservation acceptée avec succès']);
    }

    /**
     * Refuse a reservation
     */
    public function refuseReservation(Request $request, $id)
    {
        $intervenant = $request->user();

        if (!$intervenant) {
            return response()->json(['message' => 'Intervenant non authentifié'], 401);
        }

        $intervention = Intervention::where('id', $id)
            ->where('intervenant_id', $intervenant->id)
            ->first();

        if (!$intervention) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }

        $intervention->status = 'refuse';
        $intervention->save();

        return response()->json(['message' => 'Réservation refusée avec succès']);
    }
}
