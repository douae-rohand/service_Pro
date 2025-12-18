<?php

namespace App\Http\Controllers\Api\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of services with their tasks
     * Formatted for frontend consumption
     */
    public function index()
    {
        // Mettre en cache la liste des services (rarement modifiée) pour 24h
        $services = \Illuminate\Support\Facades\Cache::remember('all_services_v2', 86400, function () {
            // Optimisation : Sélectionner uniquement les colonnes nécessaires et charger les tâches de manière légère
            return Service::select('id', 'nom_service', 'description')
                          ->with(['materiels', 'taches', 'taches.materiels']) // Charge materiels du service, taches, et materiels des taches
                          ->get();
        });

        $query = Service::with(['taches', 'informations', 'justificatifs']);
        
        // Filtrer uniquement les services actifs si la colonne status existe
        if (\Illuminate\Support\Facades\Schema::hasColumn('service', 'status')) {
            $query->where('status', 'active');
        }
        
        $services = $query->get();

        // Format data for frontend
        $formattedServices = $services->map(function ($service) {
            // Get direct materials for this service
            $directMaterials = $service->materiels->map(function ($materiel) {
                return [
                    'id' => $materiel->id,
                    'name' => $materiel->nom_materiel,
                    'description' => $materiel->description,
                ];
            });

            return [
                'id' => $service->id,
                'name' => $service->nom_service,
                'description' => $service->description,
                'materials' => $directMaterials, // Direct service materials
                'tasks' => $service->taches->map(function ($tache) {
                    return [
                        'id' => $tache->id,
                        'name' => $tache->nom_tache,
                        'description' => $tache->description,
                        'status' => $tache->status,
                        'materials' => $tache->materiels->map(function ($materiel) {
                            return [
                                'id' => $materiel->id,
                                'name' => $materiel->nom_materiel,
                                'description' => $materiel->description,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'services' => $formattedServices,
        ]);
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_service' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        return response()->json([
            'message' => 'Service créé avec succès',
            'service' => $service,
        ], 201);
    }

    /**
     * Display the specified service
     * Vérifie que le service est actif avant de le retourner
     */
    public function show($id)
    {
        // Check if status column exists first
        $hasStatusColumn = \Illuminate\Support\Facades\Schema::hasColumn('service', 'status');
        
        // Build select columns
        $selectColumns = ['id', 'nom_service', 'description'];
        if ($hasStatusColumn) {
            $selectColumns[] = 'status';
        }
        
        // Optimiser : charger seulement les colonnes nécessaires des taches
        $service = Service::with(['taches:id,service_id,nom_tache,description,image_url'])
                          ->select($selectColumns)
                          ->findOrFail($id);
        
        // Vérifier si le service est actif (si la colonne existe)
        if ($hasStatusColumn) {
            if ($service->status !== 'active') {
                return response()->json([
                    'error' => 'Ce service n\'est pas disponible',
                    'message' => 'Le service demandé est actuellement désactivé'
                ], 404);
            }
        }

        return response()->json($service);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'nom_service' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
        ]);

        $service->update($validated);

        return response()->json([
            'message' => 'Service mis à jour avec succès',
            'service' => $service,
        ]);
    }

    /**
     * Remove the specified service
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'message' => 'Service supprimé avec succès',
        ]);
    }

    /**
     * Get taches for a specific service
     */
    public function getTaches($id)
    {
        $service = Service::findOrFail($id);
        // Ne charger que les taches sans relations supplémentaires pour éviter les erreurs
        $taches = $service->taches()->get();

        return response()->json($taches);
    }
}
