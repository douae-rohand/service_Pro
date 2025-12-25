<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use App\Models\Utilisateur;
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

            if (!$user instanceof Utilisateur) {
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

            // Charger les interventions liées
            $interventionIds = $reclamations->pluck('intervention_id')->filter()->unique();
            $linkedInterventions = [];
            if ($interventionIds->isNotEmpty()) {
                $linkedInterventions = Intervention::with([
                    'client.utilisateur:id,nom,prenom',
                    'intervenant.utilisateur:id,nom,prenom'
                ])
                ->whereIn('id', $interventionIds)
                ->get()
                ->keyBy('id');
            }
            
            // Collecter tous les IDs de concernant (depuis interventions et réclamations)
            $intervenantIds = collect();
            $clientIds = collect();
            
            foreach ($reclamations as $reclamation) {
                $concernantId = $reclamation->concernant_id;
                $concernantType = $reclamation->concernant_type;
                
                // Dériver depuis l'intervention si elle existe
                if ($reclamation->intervention_id && isset($linkedInterventions[$reclamation->intervention_id])) {
                    $intervention = $linkedInterventions[$reclamation->intervention_id];
                    if ($reclamation->signale_par_type === 'Client') {
                        $concernantId = $intervention->intervenant_id;
                        $concernantType = 'Intervenant';
                    } elseif ($reclamation->signale_par_type === 'Intervenant') {
                        $concernantId = $intervention->client_id;
                        $concernantType = 'Client';
                    }
                }
                
                if ($concernantId && $concernantType) {
                    if ($concernantType === 'Intervenant') {
                        $intervenantIds->push($concernantId);
                    } elseif ($concernantType === 'Client') {
                        $clientIds->push($concernantId);
                    }
                }
            }
            
            // Charger tous les intervenants et clients
            $intervenants = [];
            if ($intervenantIds->isNotEmpty()) {
                $intervenants = Intervenant::with('utilisateur:id,nom,prenom')
                    ->whereIn('id', $intervenantIds->unique())
                    ->get()
                    ->keyBy('id');
            }
            
            $clients = [];
            if ($clientIds->isNotEmpty()) {
                $clients = Client::with('utilisateur:id,nom,prenom')
                    ->whereIn('id', $clientIds->unique())
                    ->get()
                    ->keyBy('id');
            }

            // Transform data for frontend
            $reclamations->transform(function ($reclamation) use ($intervenants, $clients, $linkedInterventions) {
                $concernantName = 'N/A';
                $concernantId = $reclamation->concernant_id;
                $concernantType = $reclamation->concernant_type;
                
                // Dériver depuis l'intervention si elle existe
                if ($reclamation->intervention_id && isset($linkedInterventions[$reclamation->intervention_id])) {
                    $intervention = $linkedInterventions[$reclamation->intervention_id];
                    if ($reclamation->signale_par_type === 'Client') {
                        $concernantId = $intervention->intervenant_id;
                        $concernantType = 'Intervenant';
                    } elseif ($reclamation->signale_par_type === 'Intervenant') {
                        $concernantId = $intervention->client_id;
                        $concernantType = 'Client';
                    }
                }
                
                if ($concernantId && $concernantType) {
                    if ($concernantType === 'Intervenant' && isset($intervenants[$concernantId])) {
                        $intervenant = $intervenants[$concernantId];
                        if ($intervenant && $intervenant->utilisateur) {
                            $prenom = $intervenant->utilisateur->prenom ?? '';
                            $nom = $intervenant->utilisateur->nom ?? '';
                            $concernantName = trim($prenom . ' ' . $nom);
                            if (empty($concernantName)) {
                                $concernantName = 'N/A';
                            }
                        }
                    } elseif ($concernantType === 'Client' && isset($clients[$concernantId])) {
                        $client = $clients[$concernantId];
                        if ($client && $client->utilisateur) {
                            $prenom = $client->utilisateur->prenom ?? '';
                            $nom = $client->utilisateur->nom ?? '';
                            $concernantName = trim($prenom . ' ' . $nom);
                            if (empty($concernantName)) {
                                $concernantName = 'N/A';
                            }
                        }
                    }
                }

                return [
                    'id' => $reclamation->id,
                    'raison' => $reclamation->raison,
                    'message' => $reclamation->message,
                    'priorite' => $reclamation->priorite,
                    'statut' => $reclamation->statut,
                    'concernant_id' => $concernantId,
                    'concernant_type' => $concernantType,
                    'concernant_name' => $concernantName ?: 'N/A',
                    'intervention_id' => $reclamation->intervention_id,
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

            if (!$user instanceof Utilisateur) {
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
                'concernant_id' => 'required_without:intervention_id|integer',
                'concernant_type' => 'required_without:intervention_id|string|in:Intervenant,Client',
                'intervention_id' => 'nullable|integer|exists:intervention,id',
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

            $concernantId = $request->concernant_id;
            $concernantType = $request->concernant_type;

            // If intervention_id is provided, automatically find the intervenant
            if ($request->has('intervention_id')) {
                $intervention = \App\Models\Intervention::find($request->intervention_id);
                if (!$intervention) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Intervention non trouvée'
                    ], 404);
                }
                
                // Ensure the client owns this intervention
                if ($intervention->client_id != $clientId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cette intervention ne vous appartient pas'
                    ], 403);
                }

                $concernantId = $intervention->intervenant_id;
                $concernantType = 'Intervenant';
            } else {
                // Verify that the manually provided concerned entity exists
                if ($concernantType === 'Intervenant') {
                    $intervenant = Intervenant::find($concernantId);
                    if (!$intervenant) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Intervenant non trouvé'
                        ], 404);
                    }
                } elseif ($concernantType === 'Client') {
                    $client = Client::find($concernantId);
                    if (!$client) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Client non trouvé'
                        ], 404);
                    }
                }
            }

            $reclamation = Reclamation::create([
                'signale_par_id' => (int) $clientId,
                'signale_par_type' => 'Client',
                'concernant_id' => (int) $concernantId,
                'concernant_type' => $concernantType,
                'intervention_id' => $request->intervention_id,
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

            if (!$user instanceof Utilisateur) {
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
            // Dériver concernant depuis l'intervention si elle existe
            $concernantId = $reclamation->concernant_id;
            $concernantType = $reclamation->concernant_type;
            
            if ($reclamation->intervention_id) {
                $intervention = Intervention::with(['client.utilisateur:id,nom,prenom', 'intervenant.utilisateur:id,nom,prenom'])->find($reclamation->intervention_id);
                if ($intervention) {
                    // Si signale_par est un Client, alors concernant est l'Intervenant
                    // Si signale_par est un Intervenant, alors concernant est le Client
                    if ($reclamation->signale_par_type === 'Client') {
                        $concernantId = $intervention->intervenant_id;
                        $concernantType = 'Intervenant';
                    } elseif ($reclamation->signale_par_type === 'Intervenant') {
                        $concernantId = $intervention->client_id;
                        $concernantType = 'Client';
                    }
                }
            }
            
            if ($concernantId && $concernantType) {
                if ($concernantType === 'Intervenant') {
                    $intervenant = Intervenant::with('utilisateur:id,nom,prenom')->find($concernantId);
                    if ($intervenant && $intervenant->utilisateur) {
                        $concernantName = trim(($intervenant->utilisateur->prenom ?? '') . ' ' . ($intervenant->utilisateur->nom ?? ''));
                    }
                } elseif ($concernantType === 'Client') {
                    $client = Client::with('utilisateur:id,nom,prenom')->find($concernantId);
                    if ($client && $client->utilisateur) {
                        $concernantName = trim(($client->utilisateur->prenom ?? '') . ' ' . ($client->utilisateur->nom ?? ''));
                    }
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
                    'intervention_id' => $reclamation->intervention_id,
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

