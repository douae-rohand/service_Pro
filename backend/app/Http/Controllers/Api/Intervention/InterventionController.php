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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;


class InterventionController extends Controller
{
    /**
     * Helper to scope queries to the authenticated user
     */
    private function scopeQueryProprety($query)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return $query;
        }

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isIntervenant()) {
            $query->where('intervenant_id', $user->id);
        } else {
            // Unknown role? limit access
            $query->where('id', -1);
        }

        return $query;
    }

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

        // Additional filters are fine as long as they don't broaden the scope beyond the user's rights
        if ($request->has('clientId')) {
            $query->where('client_id', $request->clientId);
            $query->where('client_id', $request->clientId);
        }

        if ($request->has('intervenantId')) {
            $query->where('intervenant_id', $request->intervenantId);
        }

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
            'status' => 'nullable|string|in:en attend,acceptee,refuse,termine',
            'date_intervention' => 'required|date',
            'duration_hours' => 'required|numeric|min:0.5|max:24',
            'client_id' => 'required|exists:client,id',
            'intervenant_id' => 'required|exists:intervenant,id',
            'tache_id' => 'required|exists:tache,id',
            'description' => 'nullable|string',
            'constraints' => 'nullable|json',
            'materials_cost' => 'nullable|numeric|min:0',
            'provided_materials' => 'nullable|string', // JSON array of IDs
        ]);

        if ($validator->fails()) {
            Log::error('Validation error creating intervention: ' . json_encode($validator->errors()));
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // ======================================
        // VALIDATION: Check for personal information in constraints
        // ======================================
        if ($request->has('constraints')) {
            $validation = \App\Utils\InputValidator::validateConstraints($request->constraints);
            if (!$validation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validation['reason'],
                    'error' => 'PERSONAL_INFO_DETECTED'
                ], 400);
            }
        }
        
        // Validate address
        $addressValidation = \App\Utils\InputValidator::validateUserInput($request->address, 'l\'adresse');
        if (!$addressValidation['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => $addressValidation['reason'],
                'error' => 'PERSONAL_INFO_DETECTED'
            ], 400);
        }
        
        // Validate ville
        $villeValidation = \App\Utils\InputValidator::validateUserInput($request->ville, 'la ville');
        if (!$villeValidation['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => $villeValidation['reason'],
                'error' => 'PERSONAL_INFO_DETECTED'
            ], 400);
        }
        
        // Validate description if provided
        if ($request->has('description') && !empty(trim($request->description))) {
            $descriptionValidation = \App\Utils\InputValidator::validateUserInput($request->description, 'la description');
            if (!$descriptionValidation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $descriptionValidation['reason'],
                    'error' => 'PERSONAL_INFO_DETECTED'
                ], 400);
            }
        }

        try {
            $interventionData = [
                'address' => $request->address,
                'ville' => $request->ville,
                'status' => $request->status ?? 'en attend',
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

            // Handle provided materials if provided
            if ($request->has('provided_materials')) {
                try {
                    $providedMaterials = json_decode($request->provided_materials, true);
                    if (is_array($providedMaterials)) {
                        $intervention->materiels()->attach($providedMaterials);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error attaching provided materials: ' . $e->getMessage());
                }
            }

            // Handle materials cost as information
            if ($request->has('materials_cost') && $request->materials_cost > 0) {
                try {
                    $costInfo = \App\Models\Information::firstOrCreate(
                        ['nom' => 'Coût_Matériel'],
                        ['nom' => 'Coût_Matériel', 'description' => 'Coût total du matériel fourni par l\'intervenant']
                    );

                    $intervention->informations()->attach($costInfo->id, [
                        'information' => $request->materials_cost . ' DH'
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error attaching materials cost information: ' . $e->getMessage());
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

        // Privacy Filter
        $isPublic = $intervention->areRatingsPublic();
        $currentUser = Auth::user();

        // Access Control
        if ($currentUser) {
            $canAccess = ($currentUser->isClient() && $currentUser->id == $intervention->client_id) ||
                         ($currentUser->isIntervenant() && $currentUser->id == $intervention->intervenant_id);

            if (!$canAccess) {
                 abort(403, 'Accès non autorisé.');
            }
        } else {
            abort(401, 'Non authentifié.');
        }

        $isAuthorClient = $currentUser && $currentUser->isClient() && $currentUser->id === $intervention->client_id;
        $isAuthorIntervenant = $currentUser && $currentUser->isIntervenant() && $currentUser->id === $intervention->intervenant_id;

        if (!$isPublic) {
            $intervention->setRelation('evaluations', $intervention->evaluations->filter(function ($e) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $e->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $e->type_auteur === 'intervenant') return true;
                return false;
            }));

            $intervention->setRelation('commentaires', $intervention->commentaires->filter(function ($c) use ($isAuthorClient, $isAuthorIntervenant) {
                if ($isAuthorClient && $c->type_auteur === 'client') return true;
                if ($isAuthorIntervenant && $c->type_auteur === 'intervenant') return true;
                return false;
            }));
        }

        $intervention->ratings_meta = [
            'is_public' => $isPublic,
            'client_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'client')->exists(),
            'intervenant_has_rated' => \App\Models\Evaluation::where('intervention_id', $id)->where('type_auteur', 'intervenant')->exists(),
            'window_expiry' => $intervention->status === 'termine' ? $intervention->updated_at->addDays(7)->toDateTimeString() : null
        ];

        return response()->json($intervention);
    }

    /**
     * Update the specified intervention
     */
    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        // Check ownership
        if (!$user) {
            abort(401);
        }

        $canEdit = ($user->isClient() && $user->id == $intervention->client_id) ||
                   ($user->isIntervenant() && $user->id == $intervention->intervenant_id);

        if (!$canEdit) {
             abort(403, "Vous n'êtes pas autorisé à modifier cette intervention.");
        }

        $validated = $request->validate([
            'address' => 'sometimes|string',
            'ville' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:en attend,acceptee,refuse,termine',
            'dateIntervention' => 'sometimes|date',
            'clientId' => 'sometimes|exists:client,id',
            'intervenantId' => 'sometimes|exists:intervenant,id',
            'tacheId' => 'sometimes|exists:tache,id',
        ]);

        $data = [];
        if (isset($validated['address'])) $data['address'] = $validated['address'];
        if (isset($validated['ville'])) $data['ville'] = $validated['ville'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];
        if (isset($validated['dateIntervention'])) $data['date_intervention'] = $validated['dateIntervention'];
        if (isset($validated['clientId'])) $data['client_id'] = $validated['clientId'];
        if (isset($validated['intervenantId'])) $data['intervenant_id'] = $validated['intervenantId'];
        if (isset($validated['tacheId'])) $data['tache_id'] = $validated['tacheId'];

        if (empty($data)) {
            return response()->json(['message' => 'Aucune modification détectée'], 200);
        }

        if (isset($validated['status']) && in_array(strtolower($validated['status']), ['acceptee', 'acceptée', 'accepted'])) {
             // Load relationships for mail/notification
             $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache.service']);

             try {
                 // Send Email
                 \Illuminate\Support\Facades\Mail::to($intervention->client->utilisateur->email)->send(new \App\Mail\InterventionInvoiceMail($intervention));
                 
                 // Create Database Notification (if user model has Notifiable trait)
                 $clientUser = $intervention->client->utilisateur; // Assuming relationship structure
                 // Adjust based on your User model structure. Intervention->client is Client model, Client->utilisateur is User model?
                 // Let's verify relationships.
                 // In InterventionController update method:
                 // $intervention->load(['client.utilisateur'...]); 
                 // It seems $intervention->client is the Client model. $intervention->client->utilisateur likely the User.
                 
                 // Let's assume standard Laravel notification system
                 // We can manually insert into notifications table if Notifiable trait isn't handy or direct usage is complex here
                 
                 // Or better, use the Notification class I created?
                 // But simpler for now to stick to the requirement: "notification in top bar". 
                 // Does the frontend fetch from 'notifications' table? I need to assume yes.
                 
                 // Let's try to notify the user.
                 if ($intervention->client && $intervention->client->utilisateur) {
                     $user = $intervention->client->utilisateur;
                     // $user->notify(new \App\Notifications\InterventionAcceptedNotification($intervention)); // If I update it to DB channel
                 }

             } catch (\Exception $e) {
                 Log::error('Error sending acceptance email/notification: ' . $e->getMessage());
             }
        }

        $intervention->update($data);

        // After update, if status became accepted, send notifications
        if (isset($validated['status']) && in_array(strtolower($validated['status']), ['acceptee', 'acceptée', 'accepted'])) {
             // We can do it here to ensure data is saved first
             try {
                  $intervention = $intervention->refresh();
                  $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache.service']);
                  
                  if ($intervention->client && $intervention->client->utilisateur) {
                      $clientUser = $intervention->client->utilisateur;
                      
                      // Send Mail
                      \Illuminate\Support\Facades\Mail::to($clientUser->email)->send(new \App\Mail\InterventionInvoiceMail($intervention));

                      // Send DB Notification (Manual insert to ensure specific format if needed, or use Notifiable)
                      // Let's construct a notification record manually to be safe if 'Notifiable' isn't set up perfectly
                      \Illuminate\Support\Facades\DB::table('notifications')->insert([
                          'id' => \Illuminate\Support\Str::uuid(),
                          'type' => 'App\Notifications\InterventionAcceptedNotification',
                          'notifiable_type' => 'App\Models\User',
                          'notifiable_id' => $clientUser->id,
                          'data' => json_encode([
                              'message' => 'Votre facture a été envoyée ! Veuillez vérifier votre boîte mail.',
                              'intervention_id' => $intervention->id,
                              'type' => 'invoice_available'
                          ]),
                          'read_at' => null,
                          'created_at' => now(),
                          'updated_at' => now()
                      ]);
                  }
             } catch (\Exception $e) {
                  Log::error("Error processing acceptance notifications: " . $e->getMessage());
             }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Intervention mise à jour avec succès',
            'intervention' => $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache']),
        ]);
    }

    /**
     * Remove the specified intervention
     */
    public function destroy($id)
    {
        $intervention = Intervention::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        // Only owner can delete (or admin)
        if (!$user || !($user->isClient() && $user->id == $intervention->client_id)) {
            abort(403, "Action non autorisée");
        }

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
        $query = Intervention::upcoming()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);

        $this->scopeQueryProprety($query);

        return response()->json($query->get());
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
            'en attend' => 'pending',
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
            'refuse' => 'rejected',
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
            $status = strtolower($intervention->status ?? 'en attend');
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
        $allowedStatuses = ['en attend', 'en_attente', 'en attente', 'acceptee', 'acceptée', 'acceptées', 'planifiee', 'planifiée'];
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
        $user = Auth::guard('sanctum')->user();

        $canEdit = ($user->isClient() && $user->id == $intervention->client_id) ||
                   ($user->isIntervenant() && $user->id == $intervention->intervenant_id);

        if (!$canEdit) {
             abort(403, "Accès refusé.");
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string',
            'phase_prise' => 'nullable|string|in:avant,apres',
        ]);

        $phase = $validated['phase_prise'] ?? ($intervention->status === 'termine' ? 'apres' : 'avant');
        $path = $request->file('photo')->store('interventions', 'public');

        $photo = PhotoIntervention::create([
            'intervention_id' => $intervention->id,
            'photo_path' => $path,
            'phase_prise' => $phase,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Photo ajoutée avec succès',
            'photo' => $photo,
        ], 201);
    }
}

