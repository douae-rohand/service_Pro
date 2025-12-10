<?php

namespace App\Http\Controllers\Api\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index()
    {
        $services = Service::with(['taches', 'informations', 'justificatifs'])->get();

        return response()->json($services);
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomService' => 'required|string|max:100',
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
     */
    public function show($id)
    {
        $service = Service::with(['taches', 'informations', 'justificatifs'])->findOrFail($id);

        return response()->json($service);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'nomService' => 'sometimes|string|max:100',
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
        $taches = $service->taches()->with(['materiels', 'intervenants'])->get();

        return response()->json($taches);
    }
}
