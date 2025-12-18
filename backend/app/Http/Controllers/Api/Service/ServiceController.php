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
        try {
            // Start with basic query
            $query = Service::query();
            
            // Filter only active services if status column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('service', 'status')) {
                $query->where('status', 'active');
            }
            
            $services = $query->get();

            // Simple format without loading relationships first
            $formattedServices = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->nom_service,
                    'description' => $service->description,
                ];
            });

            return response()->json([
                'services' => $formattedServices,
            ]);
        } catch (\Exception $e) {
            \Log::error('Service index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to load services',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
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
    public function getTaches(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $query = $service->taches()->with(['materiels', 'intervenants']);
        
        // Filter by intervenant if provided
        if ($request->has('intervenantId')) {
            $intervenantId = $request->intervenantId;
            $query->whereHas('intervenants', function($q) use ($intervenantId) {
                $q->where('intervenant_id', $intervenantId);
            });
        }
        
        $taches = $query->get();

        return response()->json([
            'data' => $taches
        ]);
    }
}
