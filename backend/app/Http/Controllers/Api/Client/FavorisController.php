<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavorisController extends Controller
{
    /**
     * List all favorited intervenants for a client
     */
    public function index($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);
            
            // Get all favorite records for this client
            $favorisRecords = DB::table('favorise')
                ->where('client_id', $clientId)
                ->get();
                
            $favoritesData = $favorisRecords->map(function($record) use ($clientId) {
                $intervenant = \App\Models\Intervenant::with('utilisateur')->find($record->intervenant_id);
                $service = \App\Models\Service::find($record->service_id);
                
                if (!$intervenant || !$service) return null;
                
                // Get real rating info using the model's existing logic
                $ratingInfo = $intervenant->getRatingInfo();
                
                // Intervention statistics specific to this client
                $clientInterventions = \App\Models\Intervention::where('client_id', $clientId)
                    ->where('intervenant_id', $intervenant->id);
                
                $servicesWithClient = (clone $clientInterventions)->count();
                $lastIntervention = (clone $clientInterventions)->orderBy('date_intervention', 'desc')->first();
                
                // Total missions for all clients
                $totalMissions = \App\Models\Intervention::where('intervenant_id', $intervenant->id)->count();

                // Calculate average hourly rate from assigned tasks
                $hourlyRate = DB::table('intervenant_tache')
                    ->where('intervenant_id', $intervenant->id)
                    ->avg('prix_tache');

                return [
                    'id' => $intervenant->id,
                    'nom' => $intervenant->utilisateur->nom,
                    'prenom' => $intervenant->utilisateur->prenom,
                    'email' => $intervenant->utilisateur->email,
                    'photo_url' => $intervenant->utilisateur->url,
                    'ville' => $intervenant->ville,
                    'adresse' => $intervenant->address,
                    'service_id' => $record->service_id,
                    'nom_service' => $service->nom_service,
                    // Real data fields
                    'note_globale' => $ratingInfo['average_rating'],
                    'nombre_avis' => $ratingInfo['review_count'],
                    'services_avec_client' => $servicesWithClient,
                    'dernier_service' => $lastIntervention ? $lastIntervention->date_intervention : null,
                    'total_missions' => $totalMissions,
                    'tarif_horaire' => $hourlyRate ? round($hourlyRate, 0) : 25
                ];
            })->filter()->values();
                
            return response()->json([
                'status' => 'success',
                'data' => $favoritesData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching favorites: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des favoris: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite status (Add or Remove)
     */
    public function toggle(Request $request, $clientId)
    {
        $request->validate([
            'intervenant_id' => 'required|exists:intervenant,id',
            'service_id' => 'required|exists:service,id'
        ]);

        $intervenantId = $request->intervenant_id;
        $serviceId = $request->service_id;

        try {
            $exists = DB::table('favorise')
                ->where('client_id', $clientId)
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->exists();

            if ($exists) {
                // Remove from favorites
                DB::table('favorise')
                    ->where('client_id', $clientId)
                    ->where('intervenant_id', $intervenantId)
                    ->where('service_id', $serviceId)
                    ->delete();
                    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Retiré des favoris',
                    'is_favorite' => false
                ]);
            } else {
                // Add to favorites
                DB::table('favorise')->insert([
                    'client_id' => $clientId,
                    'intervenant_id' => $intervenantId,
                    'service_id' => $serviceId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Ajouté aux favoris',
                    'is_favorite' => true
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error toggling favorite: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la modification des favoris'
            ], 500);
        }
    }
    
    /**
     * Check if an intervenant is favorited
     */
    public function checkStatus(Request $request, $clientId)
    {
        $intervenantId = $request->query('intervenant_id');
        $serviceId = $request->query('service_id');
        
        if (!$intervenantId || !$serviceId) {
             return response()->json(['is_favorite' => false]);
        }

        try {
            $isFavorite = DB::table('favorise')
                ->where('client_id', $clientId)
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->exists();
                
            return response()->json([
                'is_favorite' => $isFavorite
            ]);
        } catch (\Exception $e) {
            return response()->json(['is_favorite' => false]);
        }
    }
}
