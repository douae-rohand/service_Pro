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
            
            // Allow getting favorites with service details
            $favoris = DB::table('favorise')
                ->join('intervenant', 'favorise.intervenant_id', '=', 'intervenant.id')
                ->join('utilisateur', 'intervenant.id', '=', 'utilisateur.id')
                ->join('service', 'favorise.service_id', '=', 'service.id')
                ->where('favorise.client_id', $clientId)
                ->select(
                    'intervenant.*', 
                    'utilisateur.nom', 
                    'utilisateur.prenom', 
                    'utilisateur.email', 
                    'utilisateur.url as photo_url',
                    'service.id as service_id',
                    'service.nom_service'
                )
                ->get();
                
            return response()->json([
                'status' => 'success',
                'data' => $favoris
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching favorites: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des favoris'
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
