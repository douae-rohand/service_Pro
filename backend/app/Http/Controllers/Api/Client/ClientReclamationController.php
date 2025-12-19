<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Client;
use App\Models\Intervenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientReclamationController extends Controller
{
    /**
     * Get all reclamations for the authenticated client
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::error('No authenticated user in index');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            // Load client relationship
            $user->load('client');
            
            if (!$user->client) {
                Log::error('User is not a client in index', [
                    'user_id' => $user->id,
                    'has_client' => $user->client !== null
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non autorisé - Vous devez être un client'
                ], 403);
            }

            $clientId = $user->client->id;
            
            if (!$clientId) {
                Log::error('Client ID is null in index');
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID client introuvable'
                ], 400);
            }
            
            $query = Reclamation::where('signale_par_id', $clientId)
                ->where('signale_par_type', 'Client')
                ->latest('created_at');

            // Filter by status
            if ($request->has('statut') && $request->statut !== 'all') {
                $query->where('statut', $request->statut);
            }

            // Filter by priority
            if ($request->has('priorite') && $request->priorite !== 'all') {
                $query->where('priorite', $request->priorite);
            }

            $reclamations = $query->get();

            // Load intervenants separately to avoid relationship issues
            $intervenantIds = $reclamations->where('concernant_type', 'Intervenant')
                ->pluck('concernant_id')
                ->filter()
                ->unique();
            
            $intervenants = [];
            if ($intervenantIds->isNotEmpty()) {
                $intervenants = Intervenant::with('utilisateur:id,nom,prenom')
                    ->whereIn('id', $intervenantIds)
                    ->get()
                    ->keyBy('id');
            }

            // Transform data for frontend
            $reclamations->transform(function ($reclamation) use ($intervenants) {
                $concernantName = 'N/A';
                
                if ($reclamation->concernant_type === 'Intervenant' && $reclamation->concernant_id) {
                    $intervenant = $intervenants[$reclamation->concernant_id] ?? null;
                    if ($intervenant && $intervenant->utilisateur) {
                        $concernantName = trim($intervenant->utilisateur->prenom . ' ' . $intervenant->utilisateur->nom);
                    }
                }

                return [
                    'id' => $reclamation->id,
                    'raison' => $reclamation->raison,
                    'message' => $reclamation->message,
                    'priorite' => $reclamation->priorite,
                    'statut' => $reclamation->statut,
                    'concernant_id' => $reclamation->concernant_id,
                    'concernant_type' => $reclamation->concernant_type,
                    'concernant_name' => $concernantName ?: 'N/A',
                    'notes_admin' => $reclamation->notes_admin,
                    'created_at' => $reclamation->created_at,
                    'updated_at' => $reclamation->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $reclamations
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching client reclamations: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des réclamations',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create a new reclamation
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::error('No authenticated user');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            // Load client relationship
            $user->load('client');
            
            Log::info('User attempting to create reclamation', [
                'user_id' => $user->id,
                'has_client' => $user->client !== null,
                'client_id' => $user->client ? $user->client->id : null
            ]);
            
            if (!$user->client) {
                Log::error('User is not a client', [
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non autorisé - Vous devez être un client'
                ], 403);
            }

            $clientId = $user->client->id;
            
            if (!$clientId) {
                Log::error('Client ID is null');
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID client introuvable'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'concernant_id' => 'required|integer',
                'concernant_type' => 'required|string|in:Intervenant,Client',
                'raison' => 'required|string|max:255',
                'message' => 'required|string',
                'priorite' => 'nullable|string|in:haute,moyenne,basse',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify that the concerned entity exists
            if ($request->concernant_type === 'Intervenant') {
                $intervenant = Intervenant::find($request->concernant_id);
                if (!$intervenant) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Intervenant non trouvé'
                    ], 404);
                }
            } elseif ($request->concernant_type === 'Client') {
                $client = Client::find($request->concernant_id);
                if (!$client) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Client non trouvé'
                    ], 404);
                }
            }

            $reclamation = Reclamation::create([
                'signale_par_id' => (int) $clientId,
                'signale_par_type' => 'Client',
                'concernant_id' => (int) $request->concernant_id,
                'concernant_type' => $request->concernant_type,
                'raison' => $request->raison,
                'message' => $request->message,
                'priorite' => $request->priorite ?? 'moyenne',
                'statut' => 'en_attente',
                'archived' => false,
            ]);

            Log::info('Client reclamation created', [
                'reclamation_id' => $reclamation->id,
                'client_id' => $clientId,
                'concernant_id' => $request->concernant_id,
                'concernant_type' => $request->concernant_type,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Réclamation créée avec succès',
                'data' => $reclamation
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating client reclamation: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de la réclamation',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get a specific reclamation
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            // Load client relationship
            $user->load('client');
            
            if (!$user->client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non autorisé - Vous devez être un client'
                ], 403);
            }

            $clientId = $user->client->id;

            $reclamation = Reclamation::where('id', $id)
                ->where('signale_par_id', $clientId)
                ->where('signale_par_type', 'Client')
                ->first();

            if (!$reclamation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Réclamation non trouvée'
                ], 404);
            }

            $concernantName = 'N/A';
            if ($reclamation->concernant_type === 'Intervenant' && $reclamation->concernant_id) {
                $intervenant = Intervenant::with('utilisateur:id,nom,prenom')->find($reclamation->concernant_id);
                if ($intervenant && $intervenant->utilisateur) {
                    $concernantName = trim($intervenant->utilisateur->prenom . ' ' . $intervenant->utilisateur->nom);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reclamation->id,
                    'raison' => $reclamation->raison,
                    'message' => $reclamation->message,
                    'priorite' => $reclamation->priorite,
                    'statut' => $reclamation->statut,
                    'concernant_id' => $reclamation->concernant_id,
                    'concernant_type' => $reclamation->concernant_type,
                    'concernant_name' => $concernantName,
                    'notes_admin' => $reclamation->notes_admin,
                    'created_at' => $reclamation->created_at,
                    'updated_at' => $reclamation->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching client reclamation: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de la réclamation'
            ], 500);
        }
    }
}

