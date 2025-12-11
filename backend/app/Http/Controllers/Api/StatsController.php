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
            // Compter tous les clients (Clients Satisfaits)
            $totalClients = Client::count();
            $activeClients = Client::where('is_active', true)->count();
            $nullClients = Client::whereNull('is_active')->count();
            
            // Utiliser les clients actifs ou tous les clients si aucun n'est marqué actif
            $satisfiedClients = $activeClients > 0 ? $activeClients : ($nullClients > 0 ? $nullClients : $totalClients);

            // Compter tous les intervenants (Intervenants Qualifiés)
            $totalIntervenants = Intervenant::count();
            $activeIntervenants = Intervenant::where('is_active', true)->count();
            $nullIntervenants = Intervenant::whereNull('is_active')->count();
            
            // Utiliser les intervenants actifs ou tous les intervenants si aucun n'est marqué actif
            $qualifiedIntervenants = $activeIntervenants > 0 ? $activeIntervenants : ($nullIntervenants > 0 ? $nullIntervenants : $totalIntervenants);

            // Compter les interventions terminées (Services Complétés)
            $completedServices = Intervention::where('status', 'terminée')->count();
            $totalInterventions = Intervention::count();
            
            // Si aucune intervention terminée, compter toutes les interventions
            if ($completedServices === 0) {
                $completedServices = $totalInterventions;
            }

            \Log::info('Stats calculées', [
                'satisfied_clients' => $satisfiedClients,
                'qualified_intervenants' => $qualifiedIntervenants,
                'completed_services' => $completedServices,
            ]);

            return response()->json([
                'satisfied_clients' => (int) $satisfiedClients,
                'qualified_intervenants' => (int) $qualifiedIntervenants,
                'completed_services' => (int) $completedServices,
            ]);
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

