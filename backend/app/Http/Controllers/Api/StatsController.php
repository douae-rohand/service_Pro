<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get statistics for the homepage
     * Returns:
     * - satisfied_clients: Number of active clients (or all clients)
     * - qualified_intervenants: Number of active intervenants (or all intervenants)
     * - completed_services: Number of completed interventions (or all interventions)
     */
    public function index()
    {
        try {
            // Compter tous les clients
            $allClients = Client::count();

            // Compter tous les intervenants
            $allIntervenants = Intervenant::count();

            // Compter tous les sous-services (Taches)
            // Note: On suppose que "sous services" correspond au modèle Tache
            $allSubServices = \App\Models\Tache::count();

            $stats = [
                'satisfied_clients' => (int) $allClients,
                'qualified_intervenants' => (int) $allIntervenants,
                'completed_services' => (int) $allSubServices, // Keeping key for compatibility, but value is Taches
                'clients_count' => (int) $allClients,
                'intervenants_count' => (int) $allIntervenants,
                'subservices_count' => (int) $allSubServices
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des valeurs par défaut
            \Log::error('Erreur dans StatsController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'satisfied_clients' => 0,
                'qualified_intervenants' => 0,
                'completed_services' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

