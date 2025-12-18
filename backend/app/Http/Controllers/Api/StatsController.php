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
            // Mettre en cache les statistiques pour 60 minutes
            $stats = \Illuminate\Support\Facades\Cache::remember('homepage_stats', 3600, function () {
                // Compter les clients (Clients Satisfaits)
                // Optimisation: Une seule requête pour compter
                $satisfiedClients = Client::where('is_active', true)->count();
                if ($satisfiedClients === 0) {
                    $satisfiedClients = Client::count();
                }

                // Compter les intervenants (Intervenants Qualifiés)
                $qualifiedIntervenants = Intervenant::where('is_active', true)->count();
                if ($qualifiedIntervenants === 0) {
                    $qualifiedIntervenants = Intervenant::count();
                }

                // Compter les interventions terminées (Services Complétés)
                $completedServices = Intervention::where('status', 'terminée')->count();
                if ($completedServices === 0) {
                    $completedServices = Intervention::count();
                }

                return [
                    'satisfied_clients' => (int) $satisfiedClients,
                    'qualified_intervenants' => (int) $qualifiedIntervenants,
                    'completed_services' => (int) $completedServices,
                ];
            });

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

