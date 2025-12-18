<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Http\Resources\InterventionResource;
use App\Models\Intervention;
use App\Models\Disponibilite;
use App\Models\Tache;
use App\Models\Intervenant;
use App\Models\PhotoIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{
    /**
     * Display a listing of interventions
     */
    public function index(Request $request)
    {
        $query = Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'photos',
            'evaluations.critaire',
            'commentaires',
            'facture',
            'materiels',
            'informations'
        ]);

        // Filtrer par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par client si fourni
        if ($request->has('clientId')) {
            $query->where('client_id', $request->clientId);
            $query->where('client_id', $request->clientId);
        }

        // Filtrer par intervenant si fourni
        if ($request->has('intervenantId')) {
            $query->where('intervenant_id', $request->intervenantId);
            $query->where('intervenant_id', $request->intervenantId);
        }

        $interventions = $query->orderBy('date_intervention', 'desc')->paginate(15);
        $interventions = $query->orderBy('date_intervention', 'desc')->paginate(15);

        // Use resource to transform data
        return InterventionResource::collection($interventions);
    }
    public function disponibilites($id, Request $request)
        {
            try {
                \Log::info('Fetching disponibilites for intervenant: ' . $id . ', date: ' . $request->date);
                
                $intervenant = Intervenant::findOrFail($id);
                
                // Vérifiez si une date est fournie
                if ($request->has('date')) {
                    $date = $request->date;
                    
                    // Récupérer les disponibilités qui couvrent cette date
                    $disponibilites = Disponibilite::where('intervenant_id', $id)
                        ->where(function($query) use ($date) {
                            // Pour les disponibilités régulières (jours de semaine)
                            $query->where('type', 'reguliere')
                                ->where('jours_semaine', strtolower(\Carbon\Carbon::parse($date)->locale('fr_FR')->isoFormat('dddd')));
                        })
                        ->orWhere(function($query) use ($date) {
                            // Pour les disponibilités ponctuelles (date spécifique)
                            $query->where('type', 'ponctuelle')
                                ->where('date_specific', $date);
                        })
                        ->get();
                } else {
                    // Récupérer toutes les disponibilités
                    $disponibilites = Disponibilite::where('intervenant_id', $id)->get();
                }
                
                \Log::info('Found ' . $disponibilites->count() . ' disponibilites');
                
                return response()->json([
                    'status' => 'success',
                    'data' => $disponibilites
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error fetching disponibilites: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur lors de la récupération des disponibilités',
                    'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
                ], 500);
            }
        }

    /**
     * Store a newly created intervention
     */
    public function store(Request $request)
    {
        Log::info('Creating intervention with data: ' . json_encode($request->all()));
        
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'status' => 'nullable|string|in:en_attente,confirmée,en_cours,terminée,annulée',
            'date_intervention' => 'required|date',
            'duration_hours' => 'required|numeric|min:0.5|max:24',
            'client_id' => 'required|exists:client,id',
            'intervenant_id' => 'required|exists:intervenant,id',
            'tache_id' => 'required|exists:tache,id',
            'description' => 'nullable|string',
            'constraints' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            Log::error('Validation error creating intervention: ' . json_encode($validator->errors()));
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $interventionData = [
                'address' => $request->address,
                'ville' => $request->ville,
                'status' => $request->status ?? 'en_attente',
                'date_intervention' => $request->date_intervention,
                'duration_hours' => $request->duration_hours,
                'client_id' => $request->client_id,
                'intervenant_id' => $request->intervenant_id,
                'tache_id' => $request->tache_id,
                'description' => $request->description,
            ];

            Log::info('Intervention data to create: ' . json_encode($interventionData));
            
            $intervention = Intervention::create($interventionData);
            Log::info('Intervention created with ID: ' . $intervention->id);

            // Handle photos if provided
            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
                if (is_array($photos)) {
                    foreach ($photos as $photo) {
                        if ($photo && $photo->isValid()) {
                            $path = $photo->store('intervention-photos', 'public');
                            PhotoIntervention::create([
                                'intervention_id' => $intervention->id,
                                'photo_path' => $path,
                                'phase_prise' => 'avant',
                            ]);
                        }
                    }
                } elseif ($photos && $photos->isValid()) {
                    // Single photo
                    $path = $photos->store('intervention-photos', 'public');
                    PhotoIntervention::create([
                        'intervention_id' => $intervention->id,
                        'photo_path' => $path,
                        'phase_prise' => 'avant',
                    ]);
                }
            }

            // Handle constraints if provided
            if ($request->has('constraints')) {
                try {
                    $constraints = json_decode($request->constraints, true);
                    if (is_array($constraints)) {
                        foreach ($constraints as $constraintId => $value) {
                            // Store constraints in intervention_information table
                            $constraintInfo = \App\Models\Information::firstOrCreate(
                                ['nom' => 'Contrainte_' . $constraintId],
                                ['nom' => 'Contrainte_' . $constraintId, 'description' => 'Valeur de contrainte']
                            );
                            
                            $intervention->informations()->attach($constraintInfo->id, [
                                'information' => $value
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error attaching constraints information: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Intervention créée avec succès',
                'data' => new InterventionResource($intervention->load([
                    'client.utilisateur',
                    'intervenant.utilisateur',
                    'tache.service',
                    'photos',
                    'informations'
                ])),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating intervention: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'intervention',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Display the specified intervention
     */
    public function show($id)
    {
        $intervention = Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'tache.materiels', 
            'photos',
            'evaluations.critaire',
            'commentaires',
            'facture',
            'materiels',
            'informations'
        ])->findOrFail($id);

        // Privacy Filter for evaluations and comments
        $isPublic = $intervention->areRatingsPublic();
        $currentUser = Auth::user();
        
        // Determine the role of the current user for this specific intervention
        $isAuthorClient = $currentUser && $currentUser->userable_type === 'App\Models\Client' && $currentUser->userable_id === $intervention->client_id;
        $isAuthorIntervenant = $currentUser && $currentUser->userable_type === 'App\Models\Intervenant' && $currentUser->userable_id === $intervention->intervenant_id;

        if (!$isPublic) {
            // Filter evaluations: Only keep ones written by current user
            $intervention->setRelation('evaluations', $intervention->evaluations->filter(function ($e) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $e->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $e->type_auteur === 'intervenant') return true;
                return false;
            }));

            // Filter comments
            $intervention->setRelation('commentaires', $intervention->commentaires->filter(function ($c) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $c->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $c->type_auteur === 'intervenant') return true;
                return false;
            }));
        }

        // Add additional metadata for the frontend to explain the privacy state
        $intervention->ratings_meta = [
            'is_public' => $isPublic,
            'client_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'client')->exists(),
            'intervenant_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'intervenant')->exists(),
            'window_expiry' => $intervention->status === 'termine' ? $intervention->updated_at->addDays(7)->toDateTimeString() : null
        ];

        // Privacy Filter for evaluations and comments
        $isPublic = $intervention->areRatingsPublic();
        $currentUser = Auth::user();
        
        // Determine the role of the current user for this specific intervention
        $isAuthorClient = $currentUser && $currentUser->userable_type === 'App\Models\Client' && $currentUser->userable_id === $intervention->client_id;
        $isAuthorIntervenant = $currentUser && $currentUser->userable_type === 'App\Models\Intervenant' && $currentUser->userable_id === $intervention->intervenant_id;

        if (!$isPublic) {
            // Filter evaluations: Only keep ones written by current user
            $intervention->setRelation('evaluations', $intervention->evaluations->filter(function ($e) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $e->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $e->type_auteur === 'intervenant') return true;
                return false;
            }));

            // Filter comments
            $intervention->setRelation('commentaires', $intervention->commentaires->filter(function ($c) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $c->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $c->type_auteur === 'intervenant') return true;
                return false;
            }));
        }

        // Add additional metadata for the frontend to explain the privacy state
        $intervention->ratings_meta = [
            'is_public' => $isPublic,
            'client_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'client')->exists(),
            'intervenant_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'intervenant')->exists(),
            'window_expiry' => $intervention->status === 'termine' ? $intervention->updated_at->addDays(7)->toDateTimeString() : null
        ];

        return new InterventionResource($intervention);
    }

    /**
     * Update the specified intervention
     */
    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'address' => 'sometimes|string|max:255',
            'ville' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|in:en_attente,confirmée,en_cours,terminée,annulée',
            'date_intervention' => 'sometimes|date',
            'duration_hours' => 'sometimes|numeric|min:0.5|max:24',
            'client_id' => 'sometimes|exists:client,id',
            'intervenant_id' => 'sometimes|exists:intervenant,id',
            'tache_id' => 'sometimes|exists:tache,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $intervention->update($request->only([
            'address', 'ville', 'status', 'date_intervention', 'duration_hours',
            'client_id', 'intervenant_id', 'tache_id', 'description'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Intervention mise à jour avec succès',
            'data' => new InterventionResource($intervention->load([
                'client.utilisateur',
                'intervenant.utilisateur',
                'tache.service',
                'photos',
                'informations'
            ])),
        ]);
    }

    /**
     * Remove the specified intervention
     */
    public function destroy($id)
    {
        $intervention = Intervention::findOrFail($id);
        $intervention->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Intervention supprimée avec succès',
        ]);
    }

    /**
     * Get upcoming interventions
     */
    public function upcoming()
    {
        $interventions = Intervention::upcoming()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache'])
            ->orderBy('date_intervention', 'asc')
            ->get();

        return InterventionResource::collection($interventions);
    }

    /**
     * Get completed interventions
     */
    public function completed()
    {
        $interventions = Intervention::completed()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache'])
            ->orderBy('date_intervention', 'desc')
            ->paginate(15);

        return InterventionResource::collection($interventions);
    }

    /**
     * Get client interventions with statistics
     */
    public function getClientInterventions(Request $request, $clientId)
    {
        try {
            Log::info('Fetching interventions for client_id: ' . $clientId);
            
            $count = Intervention::where('client_id', $clientId)->count();
            Log::info('Total interventions found for client_id ' . $clientId . ': ' . $count);
            
            $interventions = Intervention::with([
                'client.utilisateur',
                'intervenant.utilisateur',
                'tache.service',
                'photos',
                'evaluations.critaire',
                'commentaires',
                'facture',
                'materiels',
                'informations'
            ])
            ->where('client_id', $clientId)
            ->orderBy('date_intervention', 'desc')
            ->get();

            Log::info('Loaded ' . $interventions->count() . ' interventions with relationships for client_id: ' . $clientId);

            return response()->json([
                'status' => 'success',
                'data' => InterventionResource::collection($interventions),
                'count' => $interventions->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching client interventions: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des interventions',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Get client statistics
     */
    public function getClientStatistics($clientId)
    {
        $interventions = Intervention::where('client_id', $clientId)->get();

        $statusMap = [
            'en_attente' => 'pending',
            'en attente' => 'pending',
            'acceptee' => 'accepted',
            'acceptée' => 'accepted',
            'acceptées' => 'accepted',
            'en_cours' => 'in-progress',
            'en cours' => 'in-progress',
            'terminee' => 'completed',
            'terminée' => 'completed',
            'terminées' => 'completed',
            'annulee' => 'cancelled',
            'annulée' => 'cancelled',
            'annulées' => 'cancelled',
            'refusee' => 'rejected',
            'refusée' => 'rejected',
            'refusées' => 'rejected',
            'planifiee' => 'accepted',
            'planifiée' => 'accepted',
        ];

        $stats = [
            'pending' => 0,
            'accepted' => 0,
            'inProgress' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'rejected' => 0,
        ];

        $totalHours = 0;
        $totalCost = 0;

        foreach ($interventions as $intervention) {
            $status = strtolower($intervention->status ?? 'en_attente');
            $mappedStatus = $statusMap[$status] ?? 'pending';
            
            if (isset($stats[$mappedStatus])) {
                $stats[$mappedStatus]++;
            }

            // Add to totals if completed
            if ($mappedStatus === 'completed') {
                $totalHours += $intervention->duration_hours ?? 0;
                $totalCost += $intervention->facture->ttc ?? 0;
            }
        }

        return response()->json([
            'status_counts' => $stats,
            'total_interventions' => $interventions->count(),
            'total_hours' => round($totalHours, 1),
            'total_cost' => round($totalCost, 2),
            'average_cost_per_hour' => $totalHours > 0 ? round($totalCost / $totalHours, 2) : 0,
        ]);
    }

    /**
     * Cancel an intervention
     */
    public function cancelIntervention($id)
    {
        $intervention = Intervention::findOrFail($id);

        // Only allow cancellation if status is pending or accepted
        $allowedStatuses = ['en_attente', 'en attente', 'acceptee', 'acceptée', 'acceptées', 'planifiee', 'planifiée'];
        if (!in_array(strtolower($intervention->status ?? ''), $allowedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette intervention ne peut pas être annulée',
                'error' => 'invalid_status'
            ], 400);
        }

        $intervention->update(['status' => 'annulée']);

        return response()->json([
            'status' => 'success',
            'message' => 'Intervention annulée avec succès',
            'data' => new InterventionResource($intervention->load([
                'client.utilisateur',
                'intervenant.utilisateur',
                'tache.service',
                'photos',
                'evaluations.critaire',
                'commentaires',
                'facture',
                'materiels',
                'informations'
            ]))
        ]);
    }

    /**
     * Add a photo to an intervention
     */
    public function addPhoto(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Increased to 5MB
            'description' => 'nullable|string',
            'phase_prise' => 'nullable|string|in:avant,apres',
        ]);

        // Déterminer la phase par défaut si non fournie
        $phase = $validated['phase_prise'] ?? ($intervention->status === 'termine' ? 'apres' : 'avant');

        // Stocker la photo
        $path = $request->file('photo')->store('intervention-photos', 'public');

        $photo = PhotoIntervention::create([
            'intervention_id' => $intervention->id,
            'photo_path' => $path,
            'phase_prise' => $phase,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Photo ajoutée avec succès',
            'data' => $photo,
        ], 201);
    }
}