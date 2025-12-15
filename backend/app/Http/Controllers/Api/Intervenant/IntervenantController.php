<?php

namespace App\Http\Controllers\Api\Intervenant;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use App\Models\Disponibilite;
use App\Models\Tache;
use App\Models\Intervention;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntervenantController extends Controller
{
    /**
     * Display a listing of intervenants
     */
    public function index(Request $request)
    {
        $query = Intervenant::with(['utilisateur', 'admin.utilisateur', 'taches']);

        // Filtrer les intervenants actifs uniquement si demandé
        if ($request->has('active') && $request->active == 'true') {
            $query->active();
        }

        // Filtrer par tâche spécifique (intervenants pouvant effectuer une tâche)
        if ($request->has('tacheId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('tache.id', $request->tacheId);
            });
        }

        $intervenants = $query->get();

        return response()->json($intervenants);
    }

    /**
     * Store a newly created intervenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:utilisateur,id',
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $intervenant = Intervenant::create($validated);

        return response()->json([
            'message' => 'Intervenant créé avec succès',
            'intervenant' => $intervenant->load('utilisateur'),
        ], 201);
    }

    /**
     * Display the specified intervenant
     */
    public function show($id)
    {
        $intervenant = Intervenant::with([
            'utilisateur',
            'admin.utilisateur',
            'interventions',
            'disponibilites',
            'taches',
            'materiels',
            'clientsFavoris.utilisateur'
        ])->findOrFail($id);

        return response()->json($intervenant);
    }

    /**
     * Update the specified intervenant
     */
    public function update(Request $request, $id)
    {
        $intervenant = Intervenant::findOrFail($id);

        $validated = $request->validate([
            'address' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'isActive' => 'nullable|boolean',
            'adminId' => 'nullable|exists:admin,id',
        ]);

        $intervenant->update($validated);

        return response()->json([
            'message' => 'Intervenant mis à jour avec succès',
            'intervenant' => $intervenant->load('utilisateur'),
        ]);
    }

    /**
     * Remove the specified intervenant
     */
    public function destroy($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $intervenant->delete();

        return response()->json([
            'message' => 'Intervenant supprimé avec succès',
        ]);
    }

    /**
     * Get interventions for a specific intervenant
     */
    public function interventions($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $interventions = $intervenant->interventions()
            ->with(['client.utilisateur', 'tache'])
            ->orderBy('dateIntervention', 'desc')
            ->get();

        return response()->json($interventions);
    }

    /**
     * Get disponibilites for a specific intervenant
     */
        public function disponibilites($id, Request $request)
        {
            try {
                Log::info('Fetching disponibilites for intervenant: ' . $id . ', date: ' . ($request->date ?? 'no date'));
                
                // ✅ CORRECT: Utilisez le bon namespace App\Models\Disponibilite
                $query = Disponibilite::where('intervenant_id', $id);
                
                if ($request->has('date') && $request->date) {
                    $date = $request->date;
                    $dayOfWeek = strtolower(\Carbon\Carbon::parse($date)->locale('fr_FR')->isoFormat('dddd'));
                    
                    // Récupérer les disponibilités pour cette date
                    $query->where(function($q) use ($date, $dayOfWeek) {
                        // Régulières: jour de la semaine correspond
                        $q->where('type', 'reguliere')
                        ->where('jours_semaine', $dayOfWeek);
                    })->orWhere(function($q) use ($date) {
                        // Ponctuelles: date spécifique correspond
                        $q->where('type', 'ponctuelle')
                        ->where('date_specific', $date);
                    });
                }
                
                // Tri explicite par type et heure de début
                $disponibilites = $query->orderBy('type')
                    ->orderBy('heure_debut')
                    ->get();
                
                Log::info('Found ' . $disponibilites->count() . ' disponibilites for intervenant ' . $id);
                
                return response()->json([
                    'status' => 'success',
                    'data' => $disponibilites
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error fetching disponibilites: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur lors de la récupération des disponibilités',
                    'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
                ], 500);
            }
        }

    /**
     * Get taches that this intervenant can perform
     */
    public function taches($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $taches = $intervenant->taches()
            ->with('service')
            ->get();

        return response()->json($taches);
    }



        // Dans IntervenantController.php
    public function search(Request $request)
{
    try {
        $query = Intervenant::with(['utilisateur', 'taches.service', 'interventions'])
                    ->withAvg('evaluations', 'note')
                    ->withCount('evaluations');
        
        // Filter by city
        if ($request->has('ville') && $request->ville != 'all') {
            $query->where('ville', 'like', '%' . $request->ville . '%');
        }
        
        // Filter by service
        if ($request->has('service_id') && $request->service_id != 'all') {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }
        
        // Search by name
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('utilisateur', function ($q) use ($search) {
                $q->where('prenom', 'like', '%' . $search . '%')
                  ->orWhere('nom', 'like', '%' . $search . '%');
            });
        }
        
        // Temporairement : retirez les filtres de prix complexes
        // if ($request->has('price_min') || $request->has('price_max')) {
        //     $query->whereHas('taches', function ($q) use ($request) {
        //         if ($request->has('price_min')) {
        //             $q->where('prix_tache', '>=', $request->price_min);
        //         }
        //         if ($request->has('price_max')) {
        //             $q->where('prix_tache', '<=', $request->price_max);
        //         }
        //     });
        // }
        
        // Simplifiez le tri temporairement
        $query->latest();
        
        // Pagination
        $perPage = $request->get('per_page', 12);
        $intervenants = $query->paginate($perPage);
        
        // Transformation simplifiée
        $intervenants->getCollection()->transform(function ($intervenant) {
            $realRating = $intervenant->evaluations_avg_note ? round($intervenant->evaluations_avg_note, 1) : null;
            $realCount = $intervenant->evaluations_count;

            $intervenant->average_rating = $realRating ?? 4.0;
            $intervenant->review_count = $realCount ?? 0;
            
            return $intervenant;
        });
        
        return response()->json($intervenants);
        
    } catch (\Exception $e) {
        \Log::error('Search error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Server error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get services that this intervenant can perform
     * Returns only services where the intervenant has at least one task
     */
    public function services($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        
        \Log::info('Fetching services for intervenant ID: ' . $id);
        
        // Get all services
        $allServices = \App\Models\Service::all();
        
        // Filter services: only include services where intervenant has at least one task
        $servicesData = $allServices->map(function($service) use ($intervenant) {
            // Count tasks that this intervenant can perform for this service
            $tachesCount = \App\Models\Tache::where('service_id', $service->id)
                ->whereHas('intervenants', function($query) use ($intervenant) {
                    $query->where('intervenant_id', $intervenant->id);
                })
                ->count();

            // Only include service if intervenant has at least one task
            if ($tachesCount > 0) {
                \Log::info("Service: {$service->nom_service}, Tasks count: {$tachesCount}");
                return [
                    'id' => $service->id,
                    'nom_service' => $service->nom_service,
                    'description' => $service->description,
                    'taches_count' => $tachesCount
                ];
            }
            
            return null;
        })->filter(); // Remove null values

        \Log::info('Found ' . $servicesData->count() . ' services for intervenant');

        return response()->json([
            'data' => $servicesData->values() // Re-index array
        ]);
    }

    /**
     * Get evaluations (reviews) for a specific intervenant
     */
    public function evaluations($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        
        // Get interventions with client comments or evaluations
        $interventions = \App\Models\Intervention::where('intervenant_id', $id)
            ->where('status', 'terminée')
            ->whereHas('evaluations', function($q) {
                $q->where('type_auteur', 'client');
            })
            ->with([
                'client.utilisateur',
                'tache.service',
                'evaluations' => function($q) {
                    $q->where('type_auteur', 'client');
                },
                'commentaires' => function($q) {
                    $q->where('type_auteur', 'client');
                }
            ])
            ->orderBy('date_intervention', 'desc')
            ->get();

        $reviews = $interventions->map(function($intervention) {
            $evals = $intervention->evaluations;
            $avgRating = $evals->count() > 0 ? round($evals->avg('note'), 1) : 0;
            $comment = $intervention->commentaires->first()->commentaire ?? null;
            
            // Skip if no rating (should be covered by whereHas query but safe check)
            if ($evals->count() === 0) return null;

            return [
                'id' => $intervention->id, // Use intervention ID as review ID unique reference
                'rating' => $avgRating,
                'comment' => $comment,
                'date' => $intervention->date_intervention->format('Y-m-d'),
                'client_name' => $intervention->client->utilisateur->prenom . ' ' . substr($intervention->client->utilisateur->nom, 0, 1) . '.',
                'client_avatar' => $intervention->client->utilisateur->url,
                'service_name' => $intervention->tache->service->nom_service ?? 'Service',
                'task_name' => $intervention->tache->nom_tache ?? '',
            ];
        })->filter()->values(); // Remove nulls and reindex

        $avgTotal = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0;

        $stats = [
            'total_reviews' => $reviews->count(),
            'average_rating' => $avgTotal,
            'rating_distribution' => [
                5 => $reviews->where('rating', '>=', 4.5)->count(),
                4 => $reviews->where('rating', '>=', 3.5)->where('rating', '<', 4.5)->count(),
                3 => $reviews->where('rating', '>=', 2.5)->where('rating', '<', 3.5)->count(),
                2 => $reviews->where('rating', '>=', 1.5)->where('rating', '<', 2.5)->count(),
                1 => $reviews->where('rating', '<', 1.5)->count(),
            ]
        ];

        return response()->json([
            'stats' => $stats,
            'data' => $reviews
        ]);
    }
}
