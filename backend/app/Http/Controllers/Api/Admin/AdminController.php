<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of admins
     */
    public function index()
    {
        $admins = Admin::with('utilisateur')->get();

        return response()->json($admins);
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:utilisateur,id',
        ]);

        $admin = Admin::create($validated);

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'admin' => $admin->load('utilisateur'),
        ], 201);
    }

    /**
     * Display the specified admin
     */
    public function show($id)
    {
        $admin = Admin::with(['utilisateur', 'clients', 'intervenants'])->findOrFail($id);

        return response()->json($admin);
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        // L'admin est lié à un utilisateur, donc les mises à jour se font sur l'utilisateur
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:150|unique:utilisateur,email,' . $admin->utilisateur->id,
            'telephone' => 'sometimes|string|max:20',
        ]);

        if (!empty($validated)) {
            $admin->utilisateur->update($validated);
        }

        return response()->json([
            'message' => 'Administrateur mis à jour avec succès',
            'admin' => $admin->load('utilisateur'),
        ]);
    }

    /**
     * Remove the specified admin
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json([
            'message' => 'Administrateur supprimé avec succès',
        ]);
    }

    /**
     * Get all clients managed by this admin
     */
    public function clients($id)
    {
        $admin = Admin::findOrFail($id);
        $clients = $admin->clients()->with('utilisateur')->get();

        return response()->json($clients);
    }

    /**
     * Get all intervenants managed by this admin
     */
    public function intervenants($id)
    {
        $admin = Admin::findOrFail($id);
        $intervenants = $admin->intervenants()->with('utilisateur')->get();

        return response()->json($intervenants);
    }

    /**
     * Dashboard statistics for admin
     */
    public function dashboard($id)
    {
        $admin = Admin::findOrFail($id);

        $stats = [
            'total_clients' => $admin->clients()->count(),
            'active_clients' => $admin->clients()->where('isActive', true)->count(),
            'total_intervenants' => $admin->intervenants()->count(),
            'active_intervenants' => $admin->intervenants()->where('isActive', true)->count(),
        ];

        return response()->json($stats);
    }
}
