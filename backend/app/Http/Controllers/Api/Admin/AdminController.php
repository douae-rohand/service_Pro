<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use App\Models\Service;
use App\Models\Tache;
use App\Models\Reclamation; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Add this line
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Mail\ServiceRequestApproved;
use App\Mail\ServiceRequestRejected;
use App\Mail\ReclamationReply;
use App\Mail\ReclamationConcerned;
use App\Mail\ReclamationResolved;
use App\Mail\ServiceDeactivated;
use App\Mail\ServiceActivated;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics (with cache optimization)
     */
    public function stats()
    {
        // Cache les stats pendant 1 minute pour améliorer les performances tout en gardant des données relativement à jour
        // Utilisation d'une clé fixe pour permettre l'invalidation facile si nécessaire
        $cacheKey = 'admin_stats';
        $stats = Cache::remember($cacheKey, 60, function () {
            // Utiliser des requêtes optimisées avec select() pour limiter les colonnes
            $totalClients = Client::count();
            $totalIntervenants = Intervenant::where('is_active', true)->count();
            
            // Optimiser les requêtes de date avec des index
            $now = now();
            $lastMonthDate = $now->copy()->subMonth();
            $currentMonth = $now->month;
            $currentYear = $now->year;
            $lastMonth = $lastMonthDate->month;
            $lastMonthYear = $lastMonthDate->year;
            
            $interventionsMois = Intervention::whereMonth('date_intervention', $currentMonth)
                ->whereYear('date_intervention', $currentYear)
                ->count();
            
            // Calcul du taux de croissance avec une seule requête groupée
            $clientsStats = DB::table('client')
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->whereIn(DB::raw('MONTH(created_at)'), [$currentMonth, $lastMonth])
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->pluck('count', 'month');
            
            $clientsLastMonth = $clientsStats->get($lastMonth, 0);
            $clientsGrowth = $clientsLastMonth > 0 
                ? round((($totalClients - $clientsLastMonth) / $clientsLastMonth) * 100) 
                : 0;

            $intervenantsLastMonth = Intervenant::where('is_active', true)
                ->whereMonth('created_at', $lastMonth)
                ->whereYear('created_at', $lastMonthYear)
                ->count();
            $intervenantsGrowth = $intervenantsLastMonth > 0 
                ? round((($totalIntervenants - $intervenantsLastMonth) / $intervenantsLastMonth) * 100) 
                : 0;

            $interventionsLastMonth = Intervention::whereMonth('date_intervention', $lastMonth)
                ->whereYear('date_intervention', $lastMonthYear)
                ->count();
            $interventionsGrowth = $interventionsLastMonth > 0 
                ? round((($interventionsMois - $interventionsLastMonth) / $interventionsLastMonth) * 100) 
                : 0;

            // Optimiser le calcul de satisfaction avec une seule requête
            $evaluationStats = DB::table('evaluation')
                ->where('type_auteur', 'client')
                ->selectRaw('AVG(note) as avg_note, COUNT(*) as count')
                ->first();
            
            $noteMoyenne = $evaluationStats && $evaluationStats->count > 0 
                ? round($evaluationStats->avg_note, 1) 
                : 0;
            $satisfaction = $noteMoyenne > 0 ? round(($noteMoyenne / 5) * 100) : 0;
            $satisfactionLabel = $noteMoyenne > 0 ? $noteMoyenne . '/5' : '0/5';

            // Calcul des heures totales (estimation: 2h par intervention en moyenne)
            $totalInterventions = Intervention::count();
            $heuresTotal = $totalInterventions * 2;
            
            $interventionsLastMonthTotal = Intervention::whereMonth('date_intervention', $lastMonth)
                ->whereYear('date_intervention', $lastMonthYear)
                ->count();
            $heuresLastMonth = $interventionsLastMonthTotal * 2;
            
            $heuresGrowth = $heuresLastMonth > 0 
                ? round((($heuresTotal - $heuresLastMonth) / $heuresLastMonth) * 100) 
                : 0;

            // Calcul des demandes en attente
            // Compter les demandes avec status='demmande' pour les intervenants actifs et services actifs
            $demandesQuery = DB::table('intervenant_service')
                ->join('intervenant', 'intervenant_service.intervenant_id', '=', 'intervenant.id')
                ->join('service', 'intervenant_service.service_id', '=', 'service.id')
                ->where('intervenant.is_active', true)
                ->where('intervenant_service.status', 'demmande');
            
            // Vérifier que le service est actif si la colonne status existe
            if (Schema::hasColumn('service', 'status')) {
                $demandesQuery->where('service.status', 'active');
            }
            
            $demandesEnAttente = $demandesQuery->count();

            // Calcul des réclamations en attente (non résolues et non archivées)
            // Statuts considérés comme "en attente" : 'en_attente' et 'en_cours'
            $reclamationsNouvelles = Reclamation::where('archived', false)
                ->whereIn('statut', ['en_attente', 'en_cours'])
                ->count();

            return [
                'totalClients' => $totalClients,
                'clientsGrowth' => ($clientsGrowth >= 0 ? '+' : '') . $clientsGrowth . '%',
                'totalIntervenants' => $totalIntervenants,
                'intervenantsGrowth' => ($intervenantsGrowth >= 0 ? '+' : '') . $intervenantsGrowth . '%',
                'interventionsMois' => $interventionsMois,
                'interventionsGrowth' => ($interventionsGrowth >= 0 ? '+' : '') . $interventionsGrowth . '%',
                'satisfaction' => $satisfaction,
                'satisfactionLabel' => $satisfactionLabel,
                'heuresTotal' => $heuresTotal,
                'heuresGrowth' => ($heuresGrowth >= 0 ? '+' : '') . $heuresGrowth . '%',
                'demandesEnAttente' => $demandesEnAttente,
                'reclamationsNouvelles' => $reclamationsNouvelles
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get all clients with their user info (optimized to avoid N+1 queries)
     */
    public function getClients(Request $request)
    {
        // Charger uniquement les données nécessaires pour la liste
        $query = Client::with([
            'utilisateur:id,nom,prenom,email,telephone', // Limiter les colonnes
            'interventions:id,client_id,date_intervention,tache_id,intervenant_id,duration_hours' // Ajouter les colonnes nécessaires pour le calcul du prix
        ]);

        // Filtrer par statut
        if ($request->has('status')) {
            $isActive = $request->status === 'actif';
            $query->where('is_active', $isActive);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('utilisateur', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri par date de création décroissante (plus récent en premier)
        $query->orderBy('created_at', 'desc');

        $clients = $query->get();
        
        // Récupérer tous les IDs d'interventions en une seule fois
        $allInterventions = $clients->pluck('interventions')->flatten();
        $allInterventionIds = $allInterventions->pluck('id')->unique()->filter();
        
        // Précharger toutes les factures et évaluations en une seule requête
        $facturesByIntervention = [];
        $evaluationsByIntervention = [];
        $dernieresInterventions = collect([]);
        $prixTachesByIntervention = [];
        $prixMateriauxByIntervention = [];
        
        if ($allInterventionIds->isNotEmpty() && $allInterventions->isNotEmpty()) {
            // Récupérer toutes les factures groupées par intervention_id
            $factures = DB::table('facture')
                ->select('intervention_id', DB::raw('SUM(ttc) as total'))
                ->whereIn('intervention_id', $allInterventionIds)
                ->groupBy('intervention_id')
                ->get()
                ->keyBy('intervention_id');
            
            foreach ($factures as $interventionId => $facture) {
                $facturesByIntervention[$interventionId] = (float)$facture->total;
            }
            
            // Récupérer toutes les évaluations distinctes par intervention
            $evaluations = DB::table('evaluation')
                ->select('intervention_id')
                ->whereIn('intervention_id', $allInterventionIds)
                ->where('type_auteur', 'client')
                ->distinct()
                ->get()
                ->groupBy('intervention_id');
            
            foreach ($evaluations as $interventionId => $evals) {
                $evaluationsByIntervention[$interventionId] = $evals->count();
            }
            
            // Récupérer les dernières interventions par client
            $dernieresInterventions = DB::table('intervention')
                ->select('client_id', 'date_intervention')
                ->whereIn('client_id', $clients->pluck('id'))
                ->whereIn('id', $allInterventionIds)
                ->orderBy('date_intervention', 'desc')
                ->get()
                ->groupBy('client_id')
                ->map(function($group) {
                    return $group->first()->date_intervention;
                });
            
            // Précharger les prix des tâches pour toutes les interventions
            $interventionsData = DB::table('intervention')
                ->select('id', 'tache_id', 'intervenant_id', 'duration_hours')
                ->whereIn('id', $allInterventionIds)
                ->whereNotNull('tache_id')
                ->whereNotNull('intervenant_id')
                ->get();
            
            $tacheIds = $interventionsData->pluck('tache_id')->unique()->filter();
            $intervenantIds = $interventionsData->pluck('intervenant_id')->unique()->filter();
            
            if ($tacheIds->isNotEmpty() && $intervenantIds->isNotEmpty()) {
                // Récupérer les prix des tâches
                $prixTaches = DB::table('intervenant_tache')
                    ->select('tache_id', 'intervenant_id', 'prix_tache')
                    ->whereIn('tache_id', $tacheIds)
                    ->whereIn('intervenant_id', $intervenantIds)
                    ->get();
                
                foreach ($interventionsData as $intervention) {
                    $prixTache = $prixTaches->first(function($pt) use ($intervention) {
                        return $pt->tache_id == $intervention->tache_id && $pt->intervenant_id == $intervention->intervenant_id;
                    });
                    
                    if ($prixTache) {
                        $duration = (float)($intervention->duration_hours ?? 1);
                        $prixTachesByIntervention[$intervention->id] = (float)$prixTache->prix_tache * $duration;
                    }
                }
                
                // Récupérer les prix des matériaux pour toutes les interventions
                $materiauxInterventions = DB::table('intervention_materiel')
                    ->select('intervention_id', 'materiel_id')
                    ->whereIn('intervention_id', $allInterventionIds)
                    ->get();
                
                if ($materiauxInterventions->isNotEmpty()) {
                    $materielIds = $materiauxInterventions->pluck('materiel_id')->unique()->filter();
                    
                    $prixMateriaux = DB::table('intervenant_materiel')
                        ->select('materiel_id', 'intervenant_id', 'prix_materiel')
                        ->whereIn('materiel_id', $materielIds)
                        ->whereIn('intervenant_id', $intervenantIds)
                        ->get();
                    
                    foreach ($materiauxInterventions as $matIntervention) {
                        $intervention = $interventionsData->firstWhere('id', $matIntervention->intervention_id);
                        if ($intervention) {
                            $prixMateriel = $prixMateriaux->first(function($pm) use ($matIntervention, $intervention) {
                                return $pm->materiel_id == $matIntervention->materiel_id && $pm->intervenant_id == $intervention->intervenant_id;
                            });
                            
                            if ($prixMateriel) {
                                if (!isset($prixMateriauxByIntervention[$matIntervention->intervention_id])) {
                                    $prixMateriauxByIntervention[$matIntervention->intervention_id] = 0;
                                }
                                $prixMateriauxByIntervention[$matIntervention->intervention_id] += (float)$prixMateriel->prix_materiel;
                            }
                        }
                    }
                }
            }
        }
        
        // Mapper les clients avec les données préchargées
        $clientsData = $clients->map(function($client) use ($facturesByIntervention, $evaluationsByIntervention, $dernieresInterventions, $prixTachesByIntervention, $prixMateriauxByIntervention) {
            // Vérifier si l'utilisateur existe
            if (!$client->utilisateur) {
                return null;
            }
            
            $interventions = $client->interventions;
            $interventionsIds = $interventions->pluck('id');
            
            // Calculer le montant total depuis les données préchargées
            // Utiliser la facture si elle existe, sinon calculer à partir du prix de la tâche et des matériaux
            $montantTotal = 0;
            foreach ($interventionsIds as $interventionId) {
                if (isset($facturesByIntervention[$interventionId])) {
                    // Si une facture existe, utiliser son montant
                    $montantTotal += $facturesByIntervention[$interventionId];
                } else {
                    // Sinon, calculer à partir du prix de la tâche et des matériaux
                    $prixTache = $prixTachesByIntervention[$interventionId] ?? 0;
                    $prixMateriaux = $prixMateriauxByIntervention[$interventionId] ?? 0;
                    $montantTotal += ($prixTache + $prixMateriaux);
                }
            }
            
            // Calculer le nombre d'avis depuis les données préchargées
            $nbAvis = 0;
            foreach ($interventionsIds as $interventionId) {
                if (isset($evaluationsByIntervention[$interventionId])) {
                    $nbAvis += $evaluationsByIntervention[$interventionId];
                }
            }
            
            // Dernière intervention depuis les données préchargées
            $dernierService = $dernieresInterventions->get($client->id);
            
            return [
                'id' => $client->id,
                'nom' => $client->utilisateur->nom ?? '',
                'prenom' => $client->utilisateur->prenom ?? '',
                'email' => $client->utilisateur->email ?? '',
                'telephone' => $client->utilisateur->telephone ?? '',
                'adresse' => $client->address ?? '',
                'ville' => $client->ville ?? '',
                'reservations' => $interventions->count(),
                'dateInscription' => $client->created_at->format('Y-m-d'),
                'statut' => $client->is_active ? 'actif' : 'suspendu',
                'montantTotal' => round($montantTotal, 2),
                'dernierService' => $dernierService,
                'avis' => $nbAvis,
                // Les feedbacks seront chargés uniquement dans le modal de détails
                'feedbacks' => []
            ];
        })->filter(); // Filtrer les valeurs null

        // Use standardized pagination helper
        return $this->formatPaginatedResponse($clientsData->values(), $request, 15);
    }

    /**
     * Get client details with feedbacks (optimized - appelé uniquement lors de l'ouverture du modal)
     */
    public function getClientDetails($id)
    {
        // Limiter les colonnes chargées pour optimiser
        $client = Client::with([
            'utilisateur:id,nom,prenom,email,telephone',
            'interventions:id,client_id,date_intervention'
        ])->findOrFail($id);
        
        if (!$client->utilisateur) {
            return response()->json(['error' => 'Client non trouvé'], 404);
        }
        
        $interventions = $client->interventions;
        $interventionsIds = $interventions->pluck('id');
        
        // Calculer le montant total avec une seule requête
        $montantTotal = $interventionsIds->isNotEmpty() 
            ? \App\Models\Facture::whereIn('intervention_id', $interventionsIds)->sum('ttc')
            : 0;
        
        // Dernière intervention
        $derniereIntervention = $interventions->sortByDesc('date_intervention')->first();
        
        // Récupérer les feedbacks (uniquement ici, pas dans la liste) - optimisé
        $feedbacks = [];
        if ($interventionsIds->isNotEmpty()) {
            // Charger les évaluations avec seulement les relations nécessaires
            $evaluations = \App\Models\Evaluation::select('id', 'intervention_id', 'note', 'created_at')
                ->whereIn('intervention_id', $interventionsIds)
                ->where('type_auteur', 'client')
                ->with([
                    'intervention:id,intervenant_id,tache_id' => [
                        'intervenant:id' => ['utilisateur:id,nom,prenom'],
                        'tache:id,service_id' => ['service:id,nom_service']
                    ]
                ])
                ->get()
                ->groupBy('intervention_id');
            
            $commentaires = \App\Models\Commentaire::whereIn('intervention_id', $interventionsIds)
                ->where('type_auteur', 'client')
                ->get()
                ->keyBy('intervention_id');
            
            foreach ($evaluations as $interventionId => $evals) {
                $firstEval = $evals->first();
                if ($firstEval && $firstEval->intervention) {
                    $intervention = $firstEval->intervention;
                    $intervenant = $intervention->intervenant;
                    $tache = $intervention->tache;
                    $service = $tache ? $tache->service : null;
                    
                    $noteMoyenne = round($evals->avg('note'), 1);
                    $commentaire = $commentaires->get($interventionId);
                    
                    $feedbacks[] = [
                        'id' => $interventionId,
                        'intervenantNom' => $intervenant && $intervenant->utilisateur ? 
                            ($intervenant->utilisateur->prenom . ' ' . $intervenant->utilisateur->nom) : 'N/A',
                        'service' => $service ? $service->nom_service : 'N/A',
                        'date' => $firstEval->created_at->format('Y-m-d'),
                        'note' => $noteMoyenne,
                        'commentaire' => $commentaire ? $commentaire->commentaire : ''
                    ];
                }
            }
        }
        
        return response()->json([
            'id' => $client->id,
            'nom' => $client->utilisateur->nom ?? '',
            'prenom' => $client->utilisateur->prenom ?? '',
            'email' => $client->utilisateur->email ?? '',
            'telephone' => $client->utilisateur->telephone ?? '',
            'adresse' => $client->address ?? '',
            'ville' => $client->ville ?? '',
            'reservations' => $interventions->count(),
            'dateInscription' => $client->created_at->format('Y-m-d'),
            'statut' => $client->is_active ? 'actif' : 'suspendu',
            'montantTotal' => round($montantTotal, 2),
            'dernierService' => $derniereIntervention ? $derniereIntervention->date_intervention : null,
            'avis' => count($feedbacks),
            'feedbacks' => $feedbacks
        ]);
    }

    /**
     * Toggle client status (active/suspended)
     */
    public function toggleClientStatus($id)
    {
        $client = Client::findOrFail($id);
        $client->is_active = !$client->is_active;
        $client->save();

        return response()->json([
            'message' => $client->is_active ? 'Client activé' : 'Client suspendu',
            'statut' => $client->is_active ? 'actif' : 'suspendu'
        ]);
    }

    /**
     * Get all intervenants with their user info
     * 
     * Statut de l'intervenant : actif (is_active = true) ou suspendu (is_active = false)
     * Le statut est INDÉPENDANT des services : un intervenant peut être actif sans services
     * 
     * Affichage :
     * - Intervenant avec services : toutes les infos (personnelles + services + stats)
     * - Intervenant sans services : uniquement infos personnelles (pas de section service)
     * 
     * Filtrage :
     * - Filtre "tous" : affiche tous les intervenants (avec ou sans services)
     * - Filtre par service : exclut les intervenants sans services
     */
    public function getIntervenants(Request $request)
    {
        // Optimiser le eager loading - charger toutes les relations nécessaires
        // Note: On ne limite pas trop les colonnes pour éviter les problèmes avec les clés étrangères
        $query = Intervenant::with([
            'utilisateur', // Charger toutes les colonnes de l'utilisateur
            'taches.service', // Charger les tâches avec leur service
            'services', // Charger tous les services (nécessaire pour le pivot)
            'interventions', // Charger les interventions pour compter
            'disponibilites', // Charger les disponibilités pour l'affichage dynamique
        ]);

        // Filtrer par statut (par défaut, afficher tous)
        // Statut : 'actif' ou 'suspendu' (pas 'en_attente')
        if ($request->has('status') && $request->status !== 'tous') {
            if ($request->status === 'actif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspendu') {
                $query->where('is_active', false);
            }
        }

        // Filtrer par service
        // IMPORTANT : Si un filtre service est appliqué, exclure les intervenants sans services ACTIVÉS
        if ($request->has('service') && $request->service !== 'tous') {
            $query->whereHas('services', function($q) use ($request) {
                $q->where('nom_service', $request->service)
                  ->where('intervenant_service.status', 'active'); // Seulement les services activés pour l'intervenant
                
                // Vérifier que le service lui-même est actif
                if (Schema::hasColumn('service', 'status')) {
                    $q->where('service.status', 'active');
                }
            });
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('utilisateur', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri par date de création décroissante (plus récent en premier)
        $query->orderBy('created_at', 'desc');

        $intervenants = $query->get();
        
        // Précharger toutes les données nécessaires en une seule fois pour éviter N+1
        $allIntervenantIds = $intervenants->pluck('id');
        $allInterventionIds = $intervenants->pluck('interventions')->flatten()->pluck('id')->unique()->filter();
        
        // Précharger toutes les évaluations groupées par intervenant
        $evaluationsByIntervenant = [];
        $commentairesByIntervention = [];
        $missionsCountByIntervenant = [];
        
        if ($allInterventionIds->isNotEmpty()) {
            // Récupérer toutes les évaluations en une seule requête
            $evaluations = DB::table('evaluation')
                ->join('intervention', 'evaluation.intervention_id', '=', 'intervention.id')
                ->select(
                    'intervention.intervenant_id',
                    'evaluation.intervention_id',
                    'evaluation.note',
                    'evaluation.created_at'
                )
                ->whereIn('evaluation.intervention_id', $allInterventionIds)
                ->where('evaluation.type_auteur', 'client')
                ->get()
                ->groupBy('intervenant_id');
            
            // Grouper par intervenant puis par intervention
            foreach ($evaluations as $intervenantId => $evals) {
                $groupedByIntervention = $evals->groupBy('intervention_id');
                $evaluationsByIntervenant[$intervenantId] = $groupedByIntervention;
            }
            
            // Récupérer tous les commentaires en une seule requête
            $commentaires = DB::table('commentaire')
                ->select('intervention_id', 'commentaire')
                ->whereIn('intervention_id', $allInterventionIds)
                ->where('type_auteur', 'client')
                ->get()
                ->keyBy('intervention_id');
            
            foreach ($commentaires as $interventionId => $commentaire) {
                $commentairesByIntervention[$interventionId] = $commentaire->commentaire;
            }
            
            // Compter les missions par intervenant
            $missionsCount = DB::table('intervention')
                ->select('intervenant_id', DB::raw('COUNT(*) as count'))
                ->whereIn('intervenant_id', $allIntervenantIds)
                ->groupBy('intervenant_id')
                ->pluck('count', 'intervenant_id');
            
            foreach ($missionsCount as $intervenantId => $count) {
                $missionsCountByIntervenant[$intervenantId] = $count;
            }
        }
        
        // Mapper les intervenants avec les données préchargées
        $intervenantsData = $intervenants->map(function($intervenant) use ($request, $evaluationsByIntervenant, $commentairesByIntervention, $missionsCountByIntervenant) {
            // Vérifier si l'utilisateur existe
            if (!$intervenant->utilisateur) {
                return null;
            }
            
            // Déterminer le statut : actif ou suspendu (pas en_attente)
            $statut = $intervenant->is_active ? 'actif' : 'suspendu';
            
            // IMPORTANT : Filtrer uniquement les services ACTIVÉS (status = 'active' dans la table pivot)
            $servicesActives = $intervenant->services->filter(function($service) {
                // Vérifier que le pivot existe et a le statut 'active'
                return isset($service->pivot) && $service->pivot->status === 'active';
            });
            
            // Déterminer si l'intervenant a des services activés
            $hasServices = $servicesActives->count() > 0;
            
            // Si l'intervenant n'a pas de services, retourner seulement les infos personnelles
            if (!$hasServices) {
                return [
                    'id' => $intervenant->id,
                    'nom' => $intervenant->utilisateur->nom ?? '',
                    'prenom' => $intervenant->utilisateur->prenom ?? '',
                    'email' => $intervenant->utilisateur->email ?? '',
                    'telephone' => $intervenant->utilisateur->telephone ?? '',
                    'adresse' => $intervenant->address ?? '',
                    'ville' => $intervenant->ville ?? '',
                    'bio' => $intervenant->bio ?? '',
                    'experience' => null,
                    'service' => null,
                    'serviceId' => null,
                    'allServices' => [],
                    'allServicesWithDetails' => [], // Services avec détails (vide si pas de services)
                    'note' => 0,
                    'nbAvis' => 0,
                    'missions' => 0,
                    'dateInscription' => $intervenant->created_at ? $intervenant->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'statut' => $statut,
                    'taches' => [],
                    'disponibilite' => $this->formatDisponibilitesForIntervenant($intervenant),
                    'disponibilites' => $this->mapDisponibilites($intervenant),
                    'avis' => [],
                    'repartitionNotes' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
                ];
            }
            
            // Si un filtre service est appliqué, utiliser ce service, sinon prendre le premier
            $servicePrincipal = null;
            if ($request->has('service') && $request->service !== 'tous') {
                $servicePrincipal = $servicesActives->firstWhere('nom_service', $request->service);
            }
            
            if (!$servicePrincipal) {
                $servicePrincipal = $servicesActives->first();
            }
            
            // Utiliser les données préchargées pour les évaluations
            $evaluationsGrouped = $evaluationsByIntervenant[$intervenant->id] ?? collect();
            $avisList = [];
            $notesParIntervention = [];
            
            if ($evaluationsGrouped instanceof \Illuminate\Support\Collection && $evaluationsGrouped->isNotEmpty()) {
                foreach ($evaluationsGrouped as $interventionId => $evals) {
                    // $evals est une collection d'objets stdClass
                    $notes = $evals->pluck('note');
                    $noteMoyenne = round($notes->avg(), 1);
                    $notesParIntervention[] = round($noteMoyenne);
                    
                    $commentaire = $commentairesByIntervention[$interventionId] ?? '';
                    $firstEval = $evals->first();
                    
                    $avisList[] = [
                        'id' => $interventionId,
                        'clientNom' => 'Client', // Simplifié pour la liste (détails dans le modal)
                        'date' => isset($firstEval->created_at) ? $firstEval->created_at : now()->format('Y-m-d'),
                        'note' => $noteMoyenne,
                        'commentaire' => $commentaire
                    ];
                }
            }
            
            // Calculer la note moyenne globale
            $avgNote = count($notesParIntervention) > 0 ? round(array_sum($notesParIntervention) / count($notesParIntervention), 1) : 0;
            $nbAvis = count($avisList);
            
            // Calculer la répartition des notes
            $repartitionNotes = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = 0;
                foreach ($notesParIntervention as $note) {
                    if ($note == $i) {
                        $count++;
                    }
                }
                $repartitionNotes[$i] = $nbAvis > 0 ? round(($count / $nbAvis) * 100) : 0;
            }
            
            // Missions depuis les données préchargées
            $missions = $missionsCountByIntervenant[$intervenant->id] ?? 0;
            
            // Récupérer l'expérience depuis le pivot du service principal
            $experience = null;
            if ($servicePrincipal && isset($servicePrincipal->pivot)) {
                $experience = $this->formatExperience($servicePrincipal->pivot->experience ?? null);
            }
            
            // Construire allServicesWithDetails avec les détails (nom_service, id, experience, presentation)
            $allServicesWithDetails = $servicesActives->map(function($service) {
                return [
                    'nom_service' => $service->nom_service,
                    'id' => $service->id,
                    'experience' => $this->formatExperience($service->pivot->experience ?? null),
                    'presentation' => $service->pivot->presentation ?? null
                ];
            })->toArray();
            
            return [
                'id' => $intervenant->id,
                'nom' => $intervenant->utilisateur->nom ?? '',
                'prenom' => $intervenant->utilisateur->prenom ?? '',
                'email' => $intervenant->utilisateur->email ?? '',
                'telephone' => $intervenant->utilisateur->telephone ?? '',
                'adresse' => $intervenant->address ?? '',
                'ville' => $intervenant->ville ?? '',
                'bio' => $intervenant->bio ?? '',
                'experience' => $experience,
                'service' => $servicePrincipal ? $servicePrincipal->nom_service : 'Non assigné',
                'serviceId' => $servicePrincipal ? $servicePrincipal->id : null,
                'allServices' => $servicesActives->pluck('nom_service')->toArray(),
                'allServicesWithDetails' => $allServicesWithDetails, // Services avec détails (nom, id, experience, presentation)
                'note' => $avgNote ? round($avgNote, 1) : 0,
                'nbAvis' => $nbAvis,
                'missions' => $missions,
                'dateInscription' => $intervenant->created_at->format('Y-m-d'),
                'statut' => $intervenant->is_active ? 'actif' : 'suspendu',
                'taches' => $intervenant->taches
                    ->unique('id')
                    ->values()
                    ->map(function($tache) {
                        return [
                            'id' => $tache->id,
                            'nom' => $tache->nom_tache,
                            'tarif' => $tache->pivot->prix_tache ?? 0,
                            'description' => $tache->description,
                            'duree' => '2-3 heures',
                            'service' => $tache->service ? $tache->service->nom_service : null
                        ];
                    })
                    ->values()
                    ->toArray(),
                'disponibilite' => $this->formatDisponibilitesForIntervenant($intervenant),
                'disponibilites' => $this->mapDisponibilites($intervenant),
                'avis' => $avisList,
                'repartitionNotes' => $repartitionNotes
            ];
        })->filter(); // Filtrer les valeurs null

        // Use standardized pagination helper
        return $this->formatPaginatedResponse($intervenantsData->values(), $request, 15);
    }

    /**
     * Get intervenant details with all information (similar to getIntervenants but for a single intervenant)
     */
    public function getIntervenantDetails($id)
    {
        $intervenant = Intervenant::with([
            'utilisateur', 
            'taches.service', 
            'services', 
            'interventions.photos',
            'justificatifs',
            'disponibilites',
        ])->findOrFail($id);
        
        if (!$intervenant->utilisateur) {
            return response()->json(['error' => 'Intervenant non trouvé'], 404);
        }
        
        // Déterminer le statut : actif ou suspendu
        $statut = $intervenant->is_active ? 'actif' : 'suspendu';
        
        // Récupérer les services avec statuts actifs, désactivés ou archivés
        // Exclure les services avec status 'refuse' et 'demmande' (gérés ailleurs)
        $tousServices = $intervenant->services->filter(function($service) {
            $pivotStatus = $service->pivot->status ?? null;
            // Exclure les services refusés et en demande
            if ($pivotStatus === 'refuse' || $pivotStatus === 'demmande') {
                return false;
            }
            // Inclure seulement active, desactive, archive
            return in_array($pivotStatus, ['active', 'desactive', 'archive']);
        });
        
        // Filtrer uniquement les services ACTIVÉS pour l'affichage principal
        // 1. Le service doit être actif dans la table pivot (intervenant_service.status = 'active')
        // 2. Le service lui-même doit être actif (service.status = 'active')
        $servicesActives = $tousServices->filter(function($service) {
            // Vérifier d'abord le statut dans la table pivot
            if ($service->pivot->status !== 'active') {
                return false;
            }
            // Vérifier ensuite si le service lui-même est actif
            if (Schema::hasColumn('service', 'status')) {
                return $service->status === 'active';
            }
            // Si la colonne n'existe pas, considérer comme actif
            return true;
        });
        
        $hasServices = $servicesActives->count() > 0;
        
        // Récupérer toutes les photos (même si pas de services) (avec déduplication stricte)
        // Déduplication par ID ET par combinaison photo_path pour éviter les doublons
        $interventionsForPhotos = $intervenant->interventions;
        $photosCollection = collect();
        foreach ($interventionsForPhotos as $intervention) {
            if ($intervention->photos && is_iterable($intervention->photos)) {
                $photosCollection = $photosCollection->merge($intervention->photos);
            }
        }
        
        // Déduplication stricte par ID ET par photo_path
        $seenIds = [];
        $seenPaths = [];
        $allPhotos = [];
        
        foreach ($photosCollection as $photo) {
            $photoId = (int) $photo->id;
            $photoPath = $photo->photo_path ?? '';
            
            // Vérifier si cet ID ou ce chemin a déjà été traité
            $idSeen = in_array($photoId, $seenIds);
            $pathSeen = !empty($photoPath) && in_array($photoPath, $seenPaths);
            
            if (!$idSeen && !$pathSeen) {
                if ($photoId > 0) {
                    $seenIds[] = $photoId;
                }
                if (!empty($photoPath)) {
                    $seenPaths[] = $photoPath;
                }
                
            $allPhotos[] = [
                    'id' => $photoId,
                    'photo_path' => $photoPath,
                    'description' => $photo->description ?? null,
                    'phase_prise' => $photo->phase_prise ?? null,
                    'intervention_id' => $photo->intervention_id ?? null,
                'created_at' => $photo->created_at ? $photo->created_at->format('Y-m-d') : null,
            ];
            }
        }

        // Récupérer tous les justificatifs (même si pas de services) (avec déduplication stricte)
        // Déduplication par ID ET par combinaison type + chemin_fichier pour éviter les doublons
        $seenIds = [];
        $seenCombinations = [];
        $justificatifsList = [];
        
        foreach ($intervenant->justificatifs as $justificatif) {
            $docId = (int) $justificatif->id;
            $type = $justificatif->type ?? '';
            $cheminFichier = $justificatif->chemin_fichier ?? '';
            $combination = $type . '|' . $cheminFichier;
            
            // Vérifier si cet ID ou cette combinaison a déjà été traité
            $idSeen = in_array($docId, $seenIds);
            $combinationSeen = !empty($combination) && in_array($combination, $seenCombinations);
            
            if (!$idSeen && !$combinationSeen) {
                if ($docId > 0) {
                    $seenIds[] = $docId;
                }
                if (!empty($combination)) {
                    $seenCombinations[] = $combination;
                }
                
            $justificatifsList[] = [
                    'id' => $docId,
                    'type' => $type,
                    'chemin_fichier' => $cheminFichier,
                'created_at' => $justificatif->created_at ? $justificatif->created_at->format('Y-m-d') : null,
            ];
            }
        }

        // Si l'intervenant n'a pas de services
        if (!$hasServices) {
            // Construire allServicesWithDetailsAll même s'il n'y a pas de services actifs
            // Cela permet d'afficher les services désactivés ou archivés dans l'admin
            $allServicesWithDetailsAll = $tousServices->map(function($service) {
                $status = $service->pivot->status ?? null;
                // S'assurer que le statut est une string (et non null)
                $status = $status ? (string)$status : null;
                
                return [
                    'nom_service' => $service->nom_service,
                    'id' => $service->id,
                    'experience' => $this->formatExperience($service->pivot->experience ?? null),
                    'presentation' => $service->pivot->presentation ?? null,
                    'status' => $status // Statut: active, desactive, ou archive (toujours string)
                ];
            })->toArray();
            
            return response()->json([
                'id' => $intervenant->id,
                'nom' => $intervenant->utilisateur->nom ?? '',
                'prenom' => $intervenant->utilisateur->prenom ?? '',
                'email' => $intervenant->utilisateur->email ?? '',
                'telephone' => $intervenant->utilisateur->telephone ?? '',
                'adresse' => $intervenant->address ?? '',
                'ville' => $intervenant->ville ?? '',
                'bio' => $intervenant->bio ?? '',
                'experience' => null,
                'service' => null,
                'serviceId' => null,
                'allServices' => $tousServices->pluck('nom_service')->toArray(), // TOUS les services pour navigation
                'allServicesWithDetails' => [],
                'allServicesWithDetailsAll' => $allServicesWithDetailsAll, // TOUS les services avec détails pour toggle
                'note' => 0,
                'nbAvis' => 0,
                'missions' => 0,
                'dateInscription' => $intervenant->created_at ? $intervenant->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'statut' => $statut,
                'taches' => [],
                'disponibilite' => null,
                'avis' => [],
                'repartitionNotes' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'photos' => $allPhotos,
                'justificatifs' => $justificatifsList
            ]);
        }
        
        // Pour les intervenants avec services activés
        $interventions = $intervenant->interventions;
        $servicePrincipal = $servicesActives->first();
        
        // Calculer la note moyenne et le nombre d'avis (même logique que getIntervenants)
        $evaluationsIds = $interventions->pluck('id');
        $evaluations = \App\Models\Evaluation::whereIn('intervention_id', $evaluationsIds)
            ->where('type_auteur', 'client')
            ->with('intervention.client.utilisateur')
            ->get();
        
        // Récupérer les commentaires des clients
        $commentaires = \App\Models\Commentaire::whereIn('intervention_id', $evaluationsIds)
            ->where('type_auteur', 'client')
            ->with('intervention.client.utilisateur')
            ->get();
        
        // Grouper les évaluations par intervention_id et calculer la moyenne pour chaque intervention
        $evaluationsGrouped = $evaluations->groupBy('intervention_id');
        
        // Construire la liste des avis (1 par intervention = 1 par client)
        $avisList = [];
        $notesParIntervention = [];
        
        foreach ($evaluationsGrouped as $interventionId => $evals) {
            $firstEval = $evals->first();
            
            if ($firstEval && $firstEval->intervention && $firstEval->intervention->client && $firstEval->intervention->client->utilisateur) {
                // Calculer la note moyenne pour cette intervention
                $noteMoyenne = round($evals->avg('note'), 1);
                $notesParIntervention[] = round($noteMoyenne);
                
                $commentaire = $commentaires->firstWhere('intervention_id', $interventionId);
                
                $avisList[] = [
                    'id' => $interventionId,
                    'clientNom' => $firstEval->intervention->client->utilisateur->prenom . ' ' . 
                                   $firstEval->intervention->client->utilisateur->nom,
                    'date' => $firstEval->created_at->format('Y-m-d'),
                    'note' => $noteMoyenne,
                    'commentaire' => $commentaire ? $commentaire->commentaire : ''
                ];
            }
        }
        
        // Calculer la note moyenne globale et le nombre d'avis (= nombre d'interventions)
        $avgNote = count($notesParIntervention) > 0 ? round(array_sum($notesParIntervention) / count($notesParIntervention), 1) : 0;
        $nbAvis = count($avisList);
        
        // Calculer la répartition des notes (basée sur la note moyenne par intervention)
        $repartitionNotes = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = 0;
            foreach ($notesParIntervention as $note) {
                if ($note == $i) {
                    $count++;
                }
            }
            $repartitionNotes[$i] = $nbAvis > 0 ? round(($count / $nbAvis) * 100) : 0;
        }

        // Récupérer toutes les photos de toutes les interventions de cet intervenant (avec déduplication stricte)
        // Déduplication par ID ET par combinaison photo_path pour éviter les doublons
        $photosCollection = collect();
        foreach ($interventions as $intervention) {
            if ($intervention->photos && is_iterable($intervention->photos)) {
                $photosCollection = $photosCollection->merge($intervention->photos);
            }
        }
        
        // Déduplication stricte par ID ET par photo_path
        $seenIds = [];
        $seenPaths = [];
        $allPhotos = [];
        
        foreach ($photosCollection as $photo) {
            $photoId = (int) $photo->id;
            $photoPath = $photo->photo_path ?? '';
            
            // Vérifier si cet ID ou ce chemin a déjà été traité
            $idSeen = in_array($photoId, $seenIds);
            $pathSeen = !empty($photoPath) && in_array($photoPath, $seenPaths);
            
            if (!$idSeen && !$pathSeen) {
                if ($photoId > 0) {
                    $seenIds[] = $photoId;
                }
                if (!empty($photoPath)) {
                    $seenPaths[] = $photoPath;
                }
                
            $allPhotos[] = [
                    'id' => $photoId,
                    'photo_path' => $photoPath,
                    'description' => $photo->description ?? null,
                    'phase_prise' => $photo->phase_prise ?? null,
                    'intervention_id' => $photo->intervention_id ?? null,
                'created_at' => $photo->created_at ? $photo->created_at->format('Y-m-d') : null,
            ];
            }
        }

        // Récupérer tous les justificatifs de cet intervenant (avec déduplication stricte)
        // Déduplication par ID ET par combinaison type + chemin_fichier pour éviter les doublons
        $seenIds = [];
        $seenCombinations = [];
        $justificatifsList = [];
        
        foreach ($intervenant->justificatifs as $justificatif) {
            $docId = (int) $justificatif->id;
            $type = $justificatif->type ?? '';
            $cheminFichier = $justificatif->chemin_fichier ?? '';
            $combination = $type . '|' . $cheminFichier;
            
            // Vérifier si cet ID ou cette combinaison a déjà été traité
            $idSeen = in_array($docId, $seenIds);
            $combinationSeen = !empty($combination) && in_array($combination, $seenCombinations);
            
            if (!$idSeen && !$combinationSeen) {
                if ($docId > 0) {
                    $seenIds[] = $docId;
                }
                if (!empty($combination)) {
                    $seenCombinations[] = $combination;
                }
                
            $justificatifsList[] = [
                    'id' => $docId,
                    'type' => $type,
                    'chemin_fichier' => $cheminFichier,
                'created_at' => $justificatif->created_at ? $justificatif->created_at->format('Y-m-d') : null,
            ];
            }
        }
        
        // Récupérer l'expérience depuis le pivot du service principal (premier service actif)
        $experience = null;
        if ($servicePrincipal && isset($servicePrincipal->pivot)) {
            $experience = $this->formatExperience($servicePrincipal->pivot->experience ?? null);
        }
        
        // Construire allServicesWithDetails avec TOUS les services (active, desactive, archive uniquement)
        // Exclure refuse et demmande
        $allServicesWithDetails = $tousServices->map(function($service) {
            $status = $service->pivot->status ?? null;
            // S'assurer que le statut est une string (et non null)
            $status = $status ? (string)$status : null;
            
            return [
                'nom_service' => $service->nom_service,
                'id' => $service->id,
                'experience' => $this->formatExperience($service->pivot->experience ?? null),
                'presentation' => $service->pivot->presentation ?? null,
                'status' => $status // Statut: active, desactive, ou archive (toujours string)
            ];
        })->toArray();
        
        // Filtrer allServicesWithDetails pour ne garder que les services actifs pour l'affichage
        $allServicesWithDetailsActifs = collect($allServicesWithDetails)->filter(function($service) {
            return $service['status'] === 'active';
        })->values()->toArray();
        
        return response()->json([
            'id' => $intervenant->id,
            'nom' => $intervenant->utilisateur->nom ?? '',
            'prenom' => $intervenant->utilisateur->prenom ?? '',
            'email' => $intervenant->utilisateur->email ?? '',
            'telephone' => $intervenant->utilisateur->telephone ?? '',
            'adresse' => $intervenant->address ?? '',
            'ville' => $intervenant->ville ?? '',
            'bio' => $intervenant->bio ?? '',
            'experience' => $experience, // Experience du service principal (pour compatibilité)
            'service' => $servicePrincipal ? $servicePrincipal->nom_service : null,
            'serviceId' => $servicePrincipal ? $servicePrincipal->id : null,
            'allServices' => $tousServices->pluck('nom_service')->toArray(), // TOUS les services (actifs et désactivés) pour navigation - permet de voir tous les services dans l'admin
            'allServicesWithDetails' => $allServicesWithDetailsActifs, // Services ACTIFS avec détails (nom, id, experience) - pour affichage (limitation de 2)
            'allServicesWithDetailsAll' => $allServicesWithDetails, // TOUS les services avec détails (nom, id, experience, presentation, status) - pour toggle
            'note' => $avgNote ? round($avgNote, 1) : 0,
            'nbAvis' => $nbAvis,
            'missions' => $interventions->count(),
            'dateInscription' => $intervenant->created_at->format('Y-m-d'),
            'statut' => $statut,
            'taches' => $intervenant->taches
                ->unique('id') // Déduplication par ID pour éviter les doublons
                ->values() // Réindexer la collection
                ->map(function($tache) {
                return [
                    'id' => $tache->id,
                    'nom' => $tache->nom_tache,
                    'tarif' => $tache->pivot->prix_tache ?? 0,
                    'description' => $tache->description,
                        'duree' => '2-3 heures',
                        'service' => $tache->service ? $tache->service->nom_service : null
                ];
                })
                ->values() // Réindexer après le map
                ->toArray(),
                'disponibilite' => $this->formatDisponibilitesForIntervenant($intervenant),
                'disponibilites' => $this->mapDisponibilites($intervenant),
            'avis' => $avisList, // Liste complète des avis
            'repartitionNotes' => $repartitionNotes, // Pourcentages par étoile
            'photos' => $allPhotos, // Photos de toutes les interventions
            'justificatifs' => $justificatifsList // Justificatifs de l'intervenant
        ]);
    }

    /**
     * Toggle intervenant status (active/suspended)
     */
    public function toggleIntervenantStatus($id)
    {
        $intervenant = Intervenant::with('services')->findOrFail($id);
        $oldStatus = $intervenant->is_active;
        $intervenant->is_active = !$intervenant->is_active;
        $intervenant->save();

        // Si l'intervenant est suspendu (is_active devient false)
        // Mettre automatiquement toutes ses demandes avec status='demmande' à status='desactive'
        if (!$intervenant->is_active && $oldStatus === true) {
            $servicesAvecDemande = $intervenant->services()
                ->wherePivot('status', 'demmande')
                ->get();
            
            foreach ($servicesAvecDemande as $service) {
                $intervenant->services()->updateExistingPivot($service->id, [
                    'status' => 'desactive'
                ]);
            }
        }
        // Si l'intervenant est réactivé (is_active devient true)
        // Remettre automatiquement toutes ses demandes avec status='desactive' à status='demmande'
        if ($intervenant->is_active && $oldStatus === false) {
            $servicesDesactives = $intervenant->services()
                ->wherePivot('status', 'desactive')
                ->get();
            
            foreach ($servicesDesactives as $service) {
                $intervenant->services()->updateExistingPivot($service->id, [
                    'status' => 'demmande'
                ]);
            }
        }

        return response()->json([
            'message' => $intervenant->is_active ? 'Intervenant activé' : 'Intervenant suspendu',
            'statut' => $intervenant->is_active ? 'actif' : 'suspendu'
        ]);
    }

    /**
     * Toggle service status for an intervenant (Admin only)
     * Cette méthode permet à l'admin d'activer/désactiver un service pour un intervenant
     * sans supprimer la présentation et l'expérience
     * 
     * @param int $intervenantId
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleIntervenantServiceStatus($intervenantId, $serviceId)
    {
        try {
            $intervenant = Intervenant::findOrFail($intervenantId);
            $service = Service::findOrFail($serviceId);

            // Vérifier que la relation existe
            $existing = DB::table('intervenant_service')
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->first();

            if (!$existing) {
                return response()->json([
                    'error' => 'Relation intervenant-service non trouvée'
                ], 404);
            }

            // Toggle le statut (active <-> desactive)
            // Ne pas toucher aux statuts 'archive', 'refuse', 'demmande'
            if (!in_array($existing->status, ['active', 'desactive'])) {
                return response()->json([
                    'error' => 'Impossible de modifier le statut de ce service. Statut actuel: ' . $existing->status
                ], 400);
            }
            
            $newStatus = ($existing->status === 'active') ? 'desactive' : 'active';

            // Mettre à jour le statut SANS supprimer présentation et expérience
            DB::table('intervenant_service')
                ->where('intervenant_id', $intervenantId)
                ->where('service_id', $serviceId)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => $newStatus === 'active' ? 'Service activé pour cet intervenant' : 'Service désactivé pour cet intervenant',
                'status' => $newStatus,
                'isActive' => $newStatus === 'active'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur toggleIntervenantServiceStatus: ' . $e->getMessage(), [
                'intervenant_id' => $intervenantId,
                'service_id' => $serviceId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la modification du statut du service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a justificatif file
     */
    public function downloadJustificatif($id)
    {
        try {
            $justificatif = \App\Models\Justificatif::findOrFail($id);
            
            // Vérifier que le fichier existe
            $filePath = public_path($justificatif->chemin_fichier);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'error' => 'Fichier introuvable'
                ], 404);
            }
            
            // Extraire le nom du fichier
            $fileName = basename($justificatif->chemin_fichier);
            
            // Retourner le fichier avec les headers appropriés
            return response()->download($filePath, $fileName, [
                'Content-Type' => mime_content_type($filePath),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du téléchargement du fichier',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending intervenant requests
     * Retourne uniquement les demandes avec status='demmande' pour les intervenants actifs (is_active=true)
     * - Uniquement les intervenants actifs (is_active = true)
     * - Uniquement les demandes avec status='demmande' (exclut 'desactive', 'refuse', 'active' et null)
     */
    public function getPendingRequests(Request $request)
    {
        try {
            // ========================================================================
            // LOGIQUE DE FILTRAGE - AdminDemandes
            // ========================================================================
            // Cette méthode affiche UNIQUEMENT les demandes qui satisfont TOUTES ces conditions :
            // 1. Intervenant ACTIF (is_active = true)
            // 2. Service ACTIF (service.status = 'active')
            // 3. Demande EN ATTENTE (intervenant_service.status = 'demmande')
            //
            // IMPORTANT : Si un intervenant ou un service est désactivé APRÈS la création
            // de la demande, le statut 'demmande' reste INCHANGÉ en base de données,
            // mais la demande DISPARAÎT de cette liste (filtrage dynamique).
            // Si l'intervenant ou le service est réactivé, la demande RÉAPPARAÎT automatiquement.
            // ========================================================================
            
            $query = Intervenant::with(['utilisateur', 'services', 'taches.service', 'justificatifs'])
                ->where('is_active', true) // FILTRE 1 : Uniquement les intervenants actifs
                ->whereHas('services', function($q) {
                    // FILTRE 2 : Seulement les services avec status = 'demmande' dans la table pivot
                    $q->where('intervenant_service.status', 'demmande');
                    // FILTRE 3 : Filtrer uniquement les services ACTIFS (service.status = 'active')
                    // Cela garantit qu'on ne montre que les demandes pour des services actifs
                    if (Schema::hasColumn('service', 'status')) {
                        $q->where('service.status', 'active');
                    }
                });

            // Filtrer par service si fourni
            if ($request->has('service') && $request->service !== 'tous' && $request->service !== 'all') {
                $query->whereHas('services', function($q) use ($request) {
                    $q->where('nom_service', $request->service)
                      ->where('intervenant_service.status', 'demmande'); // Uniquement status='demmande'
                    // IMPORTANT : Filtrer uniquement les services ACTIFS
                    if (Schema::hasColumn('service', 'status')) {
                        $q->where('service.status', 'active');
                    }
                });
            }

            // Recherche par nom, prénom ou email
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->whereHas('utilisateur', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $pendingRequests = collect();
            
            // Tri par date de création décroissante (plus récent en premier)
            $query->orderBy('created_at', 'desc');
            
            $pendingIntervenants = $query->get();
            
            foreach ($pendingIntervenants as $intervenant) {
                // Vérifier si l'utilisateur existe
                if (!$intervenant->utilisateur) {
                    continue;
                }

                // IMPORTANT : Filtrer uniquement les services avec status = 'demmande' ET service actif
                // Un intervenant peut avoir des services activés ET des services en attente
                // Mais on ne doit afficher que les demandes pour les services ACTIFS
                $servicesEnAttente = $intervenant->services->filter(function($service) {
                    $status = $service->pivot->status;
                    // Vérifier que le statut de la demande est 'demmande'
                    if ($status !== 'demmande') {
                        return false;
                    }
                    // IMPORTANT : Vérifier aussi que le service lui-même est actif
                    if (Schema::hasColumn('service', 'status')) {
                        return $service->status === 'active';
                    }
                    // Si la colonne status n'existe pas, considérer comme actif
                    return true;
                });

                // Vérifier que l'intervenant a bien des services en attente
                if ($servicesEnAttente->count() === 0) {
                    continue; // Ne pas retourner les intervenants sans services en attente
                }

                // Récupérer tous les justificatifs de cet intervenant (avec déduplication stricte)
                // Déduplication par ID ET par combinaison type + chemin_fichier pour éviter les doublons
                $seenIds = [];
                $seenCombinations = [];
                $justificatifsList = [];
                
                foreach ($intervenant->justificatifs as $justificatif) {
                    $docId = (int) $justificatif->id;
                    $type = $justificatif->type ?? '';
                    $cheminFichier = $justificatif->chemin_fichier ?? '';
                    $combination = $type . '|' . $cheminFichier;
                    
                    // Vérifier si cet ID ou cette combinaison a déjà été traité
                    $idSeen = in_array($docId, $seenIds);
                    $combinationSeen = !empty($combination) && in_array($combination, $seenCombinations);
                    
                    if (!$idSeen && !$combinationSeen) {
                        if ($docId > 0) {
                            $seenIds[] = $docId;
                        }
                        if (!empty($combination)) {
                            $seenCombinations[] = $combination;
                        }
                        
                        $justificatifsList[] = [
                            'id' => $docId,
                            'type' => $type,
                            'chemin_fichier' => $cheminFichier,
                            'created_at' => $justificatif->created_at ? $justificatif->created_at->format('Y-m-d') : null,
                        ];
                    }
                }

                // Créer une entrée DISTINCTE pour CHAQUE service en attente
                foreach ($servicesEnAttente as $service) {
                    $pendingRequests->push([
                        'id' => $intervenant->id,
                        'service_id' => $service->id, // ID du service spécifique
                        'request_id' => $intervenant->id . '_' . $service->id, // Identifiant unique pour cette demande (intervenant + service)
                        'prenom' => $intervenant->utilisateur->prenom ?? '',
                        'nom' => $intervenant->utilisateur->nom ?? '',
                        'email' => $intervenant->utilisateur->email ?? '',
                        'telephone' => $intervenant->utilisateur->telephone ?? '',
                        'adresse' => $intervenant->address ?? '',
                        'ville' => $intervenant->ville ?? '',
                        'bio' => $intervenant->bio ?? '',
                        'experience' => $this->formatExperience($service->pivot->experience ?? null),
                        'presentation' => $service->pivot->presentation ?? null, // Présentation spécifique au service
                        'service' => $service->nom_service, // Un seul service par entrée
                        'statut' => $service->pivot->status ?? 'demmande', // Statut du service dans le pivot
                        'dateInscription' => $intervenant->created_at ? $intervenant->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                        'dateAttente' => $this->calculateWaitingTime($service->pivot->created_at ?? $intervenant->created_at ?? now()),
                        'photo' => null, // Placeholder for photo
                        'taches' => $intervenant->taches->filter(function($tache) use ($service) {
                            // Filtrer les tâches pour ce service spécifique
                            return $tache->service && $tache->service->id === $service->id;
                        })->map(function($tache) {
                            return [
                                'id' => $tache->id ?? null,
                                'nom' => $tache->nom_tache ?? '',
                                'tarif' => $tache->pivot->prix_tache ?? 0,
                                'service' => $tache->service ? $tache->service->nom_service : null
                            ];
                        })->values()->toArray(),
                        'justificatifs' => $justificatifsList // Documents justificatifs
                    ]);
                }
            }

            return response()->json($pendingRequests->values());
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des demandes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération des demandes',
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    /**
     * Approve intervenant request
     * Approuve la demande d'un intervenant pour un service spécifique
     * Accepte service_id dans le body de la requête pour approuver uniquement ce service
     */
    public function approveRequest(Request $request, $id)
    {
        try {
            $intervenant = Intervenant::with(['utilisateur', 'services', 'taches'])->findOrFail($id);
            
            // Vérifier que l'intervenant a bien un utilisateur
            if (!$intervenant->utilisateur) {
                return response()->json([
                    'error' => 'Utilisateur non trouvé',
                    'message' => 'L\'intervenant n\'a pas d\'utilisateur associé.'
                ], 404);
            }

            // Récupérer le service_id depuis le body de la requête
            $serviceId = $request->input('service_id');
            
            if (!$serviceId) {
                return response()->json([
                    'error' => 'Service ID manquant',
                    'message' => 'Le service_id est requis pour approuver une demande.'
                ], 400);
            }

            // Trouver le service spécifique dans les services de l'intervenant
            $service = $intervenant->services->firstWhere('id', $serviceId);
            
            if (!$service) {
                return response()->json([
                    'error' => 'Service non trouvé',
                    'message' => 'Ce service n\'est pas associé à cet intervenant.'
                ], 404);
            }

            // Vérifier que le service est en attente (pas déjà activé)
            $status = $service->pivot->status;
            if ($status === 'active') {
                return response()->json([
                    'error' => 'Service déjà activé',
                    'message' => 'Ce service est déjà activé pour cet intervenant.'
                ], 400);
            }

            // Vérifier que le service est bien en attente
            if ($status !== 'demmande' && $status !== 'desactive' && $status !== null) {
                return response()->json([
                    'error' => 'Statut invalide',
                    'message' => 'Ce service n\'est pas en attente d\'approbation.'
                ], 400);
            }

            // Vérifier la limite de 2 services actifs maximum par intervenant
            $servicesActifsCount = $this->countActiveServices($intervenant);
            if ($servicesActifsCount >= 2) {
                // Refuser automatiquement la demande car l'intervenant a déjà 2 services actifs
                $intervenant->services()->updateExistingPivot($serviceId, [
                    'status' => 'refuse',
                    'updated_at' => now()
                ]);

                $serviceName = $service->nom_service;
                $servicesActifs = $intervenant->services()
                    ->wherePivot('status', 'active')
                    ->pluck('nom_service')
                    ->toArray();

                // Envoyer un email pour informer l'intervenant
                try {
                    $messageRejet = "Vous êtes déjà admis à 2 services (" . implode(', ', $servicesActifs) . "). Cette demande pour le service '{$serviceName}' a été automatiquement refusée.";
                    Mail::to($intervenant->utilisateur->email)->send(
                        new ServiceRequestRejected(
                            $intervenant->utilisateur,
                            $messageRejet
                        )
                    );
                } catch (\Exception $emailException) {
                    Log::error("Erreur lors de l'envoi de l'email de refus automatique", [
                        'email' => $intervenant->utilisateur->email,
                        'error' => $emailException->getMessage()
                    ]);
                }

                return response()->json([
                    'error' => 'Limite de services atteinte',
                    'message' => 'Cet intervenant est déjà admis à 2 services. La demande a été automatiquement refusée.',
                    'services_actifs' => $servicesActifs,
                    'service_refuse' => $serviceName
                ], 400);
            }

            // Activer l'intervenant (si ce n'est pas déjà le cas)
            // Un intervenant peut être actif et demander un nouveau service
            if (!$intervenant->is_active) {
                $intervenant->is_active = true;
                $intervenant->save();
            }

            // Activer UNIQUEMENT le service spécifié
            // Note: updateExistingPivot préserve automatiquement les colonnes non spécifiées (presentation, experience, etc.)
            $intervenant->services()->updateExistingPivot($serviceId, [
                'status' => 'active',
                'updated_at' => now()
                // La présentation et l'expérience sont préservées automatiquement
            ]);
            
            $serviceName = $service->nom_service;

            // Notify Intervenant via SSE
            \Illuminate\Support\Facades\Cache::put("intervenant_request_update_{$id}", [
                'status' => 'active',
                'service' => $serviceName,
                'message' => "Votre demande pour le service '{$serviceName}' a été acceptée.",
                'timestamp' => time()
            ], 60);

            // Récupérer tous les services activés (anciens + nouveau) pour le retour
            $intervenant->refresh();
            $tousServicesActives = $intervenant->services()
                ->wherePivot('status', 'active')
                ->pluck('nom_service')
                ->toArray();

            // Log de l'action
            $this->logAdminAction('approve_request', 'intervenant', $id, 
                "Demande d'intervenant ID {$id} approuvée pour le service: {$serviceName}");

            // Invalider le cache des stats pour mettre à jour immédiatement les badges
            Cache::forget('admin_stats');

            // Envoyer un email à l'intervenant
            try {
                $emailSent = Mail::to($intervenant->utilisateur->email)->send(
                    new ServiceRequestApproved($intervenant->utilisateur, [$serviceName])
                );
                Log::info("Email d'acceptation envoyé à l'intervenant ID {$id} ({$intervenant->utilisateur->email})", [
                    'email' => $intervenant->utilisateur->email,
                    'service' => $serviceName
                ]);
            } catch (\Exception $emailException) {
                // Log l'erreur détaillée mais ne bloque pas la réponse
                Log::error("Erreur lors de l'envoi de l'email d'acceptation à l'intervenant ID {$id}", [
                    'email' => $intervenant->utilisateur->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
            }

            return response()->json([
                'message' => 'Demande de service approuvée avec succès',
                'intervenant' => [
                    'id' => $intervenant->id,
                    'nom' => $intervenant->utilisateur->nom,
                    'prenom' => $intervenant->utilisateur->prenom,
                    'services' => $tousServicesActives, // Tous les services activés (anciens + nouveau)
                    'nouveauService' => $serviceName // Le nouveau service activé
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Intervenant non trouvé lors de l\'approbation: ' . $id);
            return response()->json([
                'error' => 'Intervenant non trouvé',
                'message' => 'L\'intervenant demandé n\'existe pas.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation de la demande: ' . $e->getMessage(), [
                'intervenant_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de l\'approbation',
                'message' => 'Une erreur est survenue lors de l\'approbation de la demande.'
            ], 500);
        }
    }

    /**
     * Reject intervenant request
     * Rejette la demande pour un service spécifique mais garde l'intervenant dans la base de données
     * Accepte service_id dans le body de la requête pour rejeter uniquement ce service
     */
    public function rejectRequest(Request $request, $id)
    {
        try {
            $intervenant = Intervenant::with(['utilisateur', 'services', 'taches'])->findOrFail($id);
            
            // Vérifier que l'intervenant a bien un utilisateur
            if (!$intervenant->utilisateur) {
                return response()->json([
                    'error' => 'Utilisateur non trouvé',
                    'message' => 'L\'intervenant n\'a pas d\'utilisateur associé.'
                ], 404);
            }

            // Récupérer le service_id depuis le body de la requête
            $serviceId = $request->input('service_id');
            
            if (!$serviceId) {
                return response()->json([
                    'error' => 'Service ID manquant',
                    'message' => 'Le service_id est requis pour rejeter une demande.'
                ], 400);
            }

            // Trouver le service spécifique dans les services de l'intervenant
            $service = $intervenant->services->firstWhere('id', $serviceId);
            
            if (!$service) {
                return response()->json([
                    'error' => 'Service non trouvé',
                    'message' => 'Ce service n\'est pas associé à cet intervenant.'
                ], 404);
            }

            // Vérifier que le service est en attente (pas déjà activé)
            $status = $service->pivot->status;
            if ($status === 'active') {
                return response()->json([
                    'error' => 'Service déjà activé',
                    'message' => 'Ce service est déjà activé. Vous ne pouvez pas rejeter un service actif.'
                ], 400);
            }

            // Vérifier que le service est bien en attente (ne pas rejeter une demande déjà refusée)
            if ($status !== 'demmande' && $status !== 'desactive' && $status !== null) {
                return response()->json([
                    'error' => 'Statut invalide',
                    'message' => 'Ce service n\'est pas en attente d\'approbation.'
                ], 400);
            }

            // Récupérer le nom du service avant de changer le statut (pour le log)
            $serviceName = $service->nom_service;
            
            // Mettre le statut à 'refuse' au lieu de détacher le service
        // Cela permet de conserver l'historique et de ne pas afficher cette demande dans la liste
        $intervenant->services()->updateExistingPivot($serviceId, [
            'status' => 'refuse',
            'updated_at' => now()
        ]);

        // Notify Intervenant via SSE
        \Illuminate\Support\Facades\Cache::put("intervenant_request_update_{$id}", [
            'status' => 'refuse',
            'service' => $serviceName,
            'message' => "Votre demande pour le service '{$serviceName}' a été refusée.",
            'timestamp' => time()
        ], 60);
        
        // L'intervenant reste dans la base
            // Si c'était sa première demande et qu'elle est rejetée, is_active reste false
            // Si c'était une demande supplémentaire, is_active reste true et les services activés restent
            // Les tâches associées restent également (elles peuvent être réactivées si la demande est réapprouvée plus tard)
            
            // Log de l'action
            $this->logAdminAction('reject_request', 'intervenant', $id, 
                "Demande d'intervenant ID {$id} rejetée pour le service: {$serviceName}. Statut mis à 'refuse'. Intervenant conservé dans la base.");

            // Invalider le cache des stats pour mettre à jour immédiatement les badges
            Cache::forget('admin_stats');

            // Envoyer un email à l'intervenant
            try {
                $emailSent = Mail::to($intervenant->utilisateur->email)->send(
                    new ServiceRequestRejected($intervenant->utilisateur, [$serviceName])
                );
                Log::info("Email de rejet envoyé à l'intervenant ID {$id} ({$intervenant->utilisateur->email})", [
                    'email' => $intervenant->utilisateur->email,
                    'service' => $serviceName
                ]);
            } catch (\Exception $emailException) {
                // Log l'erreur détaillée mais ne bloque pas la réponse
                Log::error("Erreur lors de l'envoi de l'email de rejet à l'intervenant ID {$id}", [
                    'email' => $intervenant->utilisateur->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
            }

            return response()->json([
                'message' => 'Demande de service rejetée avec succès. L\'intervenant reste dans la liste des intervenants.',
                'intervenant' => [
                    'id' => $intervenant->id,
                    'nom' => $intervenant->utilisateur->nom,
                    'prenom' => $intervenant->utilisateur->prenom,
                    'statut' => $intervenant->is_active ? 'actif' : 'suspendu',
                    'serviceRejete' => $serviceName
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Intervenant non trouvé lors du rejet: ' . $id);
            return response()->json([
                'error' => 'Intervenant non trouvé',
                'message' => 'L\'intervenant demandé n\'existe pas.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet de la demande: ' . $e->getMessage(), [
                'intervenant_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors du rejet',
                'message' => 'Une erreur est survenue lors du rejet de la demande.'
            ], 500);
        }
    }

    /**
     * Get all services with stats (optimized to avoid N+1 queries)
     */
    public function getServices(Request $request)
    {
        // Get all services without filtering
        $services = \App\Models\Service::select('id', 'nom_service', 'description', 'status')->get();
        $serviceIds = $services->pluck('id');
        
        // Précharger toutes les statistiques en une seule fois
        // Compter les intervenants actifs par service
        $activeIntervenantsByService = DB::table('intervenant_service')
            ->join('intervenant', 'intervenant_service.intervenant_id', '=', 'intervenant.id')
            ->select('intervenant_service.service_id', DB::raw('COUNT(DISTINCT intervenant.id) as count'))
            ->whereIn('intervenant_service.service_id', $serviceIds)
            ->where('intervenant.is_active', true)
            ->where('intervenant_service.status', 'active')
            ->groupBy('intervenant_service.service_id')
            ->pluck('count', 'service_id');
        
        // Compter les missions par service
        $missionsByService = DB::table('intervention')
            ->join('tache', 'intervention.tache_id', '=', 'tache.id')
            ->select('tache.service_id', DB::raw('COUNT(*) as count'))
            ->whereIn('tache.service_id', $serviceIds)
            ->groupBy('tache.service_id')
            ->pluck('count', 'service_id');
        
        // Calculer la note moyenne par service
        $notesByService =DB::table('evaluation')
            ->join('intervention', 'evaluation.intervention_id', '=', 'intervention.id')
            ->join('tache', 'intervention.tache_id', '=', 'tache.id')
            ->select('tache.service_id', DB::raw('AVG(evaluation.note) as avg_note'))
            ->whereIn('tache.service_id', $serviceIds)
            ->where('evaluation.type_auteur', 'client')
            ->groupBy('tache.service_id')
            ->pluck('avg_note', 'service_id');
        
        $servicesData = $services->map(function($service) use ($activeIntervenantsByService, $missionsByService, $notesByService) {
            $intervenants = $activeIntervenantsByService->get($service->id, 0);
            $missions = $missionsByService->get($service->id, 0);
            $avgNote = $notesByService->get($service->id);
            $avgNote = $avgNote ? round($avgNote, 1) : 0;

            return [
                'id' => $service->id,
                'nom_service' => $service->nom_service ?? '',
                'description' => $service->description ?? '',
                'intervenants' => $intervenants,
                'missions' => $missions,
                'note' => $avgNote,
                'couleur' => $service->nom_service === 'Jardinage' ? '#92B08B' : ($service->nom_service === 'Ménage' ? '#5B7C99' : '#808080'),
                'image' => null,
                'status' => $service->status ?? 'active',
                'isActive' => ($service->status ?? 'active') === 'active'
            ];
        });

        // Use standardized pagination helper
        return $this->formatPaginatedResponse($servicesData, $request, 15);
    }

    /**
     * Create a new service
     */
    public function createService(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom_service' => 'required|string|max:100',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $service = \App\Models\Service::create([
                'nom_service' => $validated['nom_service'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            Log::info("Service créé", [
                'service_id' => $service->id,
                'nom_service' => $service->nom_service
            ]);

            return response()->json([
                'message' => 'Service créé avec succès',
                'service' => [
                    'id' => $service->id,
                    'nom_service' => $service->nom_service,
                    'description' => $service->description,
                    'status' => $service->status,
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur createService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors de la création du service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle service status
     * 
     * Effets de la désactivation :
     * - Le service n'est plus visible pour les clients (ServiceController::index())
     * - Les clients ne peuvent plus accéder aux détails du service (ServiceController::show())
     * - Les nouvelles demandes ne peuvent plus être créées pour ce service
     * - Les missions en cours ne sont PAS affectées (elles continuent normalement)
     * - Les intervenants associés au service ne sont PAS désactivés
     * - Les statistiques restent accessibles pour l'admin
     */
    public function toggleServiceStatus($id)
    {
        try {
            $service = \App\Models\Service::findOrFail($id);
            
            // Vérifier si la colonne status existe, sinon utiliser une valeur par défaut
            if (Schema::hasColumn('service', 'status')) {
                $oldStatus = $service->status ?? 'active';
                $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';
                
                $service->status = $newStatus;
                $service->save();
                
                // Log de l'action
                Log::info("Service status changed", [
                    'service_id' => $id,
                    'service_name' => $service->nom_service,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
                
                // Mettre à jour les demandes liées au service en fonction du statut du service
                // - Si le service est désactivé : passer toutes les demandes (status='demmande') à 'desactive'
                // - Si le service est réactivé : remettre ces demandes à 'demmande' MAIS seulement pour les intervenants actifs
                //   et en respectant la limite de 2 services actifs
                if ($newStatus === 'inactive') {
                    // Désactiver toutes les demandes en attente pour ce service
                    DB::table('intervenant_service')
                        ->where('service_id', $service->id)
                        ->where('status', 'demmande')
                        ->update([
                            'status' => 'desactive',
                            'updated_at' => now(),
                        ]);
                } else { // $newStatus === 'active'
                    // Réactiver les demandes, mais seulement pour les intervenants actifs
                    // et en respectant la limite de 2 services actifs
                    // On laisse handleServiceReactivation gérer cela correctement
                    // Ne pas remettre automatiquement toutes les demandes à 'demmande' ici
                    // car certaines peuvent avoir été mises à 'desactive' pour d'autres raisons
                }
                
                // Si le service est réactivé, vérifier la limite de 2 services actifs par intervenant
                // Cette méthode gère aussi la réactivation des demandes appropriées
                // Elle retourne la liste des IDs des intervenants qui ont été exclus (déjà 2 services actifs)
                $excludedIntervenantIds = [];
                if ($newStatus === 'active') {
                    $excludedIntervenantIds = $this->handleServiceReactivation($service);
                }
                
                // Envoyer des emails aux intervenants qui ont demandé ce service
                // Mais exclure ceux qui ont déjà 2 services actifs (ils ont déjà reçu un email de refus)
                $this->notifyIntervenantsAboutServiceStatus($service, $newStatus, $excludedIntervenantIds);
                
                $message = $newStatus === 'active' 
                    ? 'Service activé avec succès. Il est maintenant visible pour les clients. Tous les intervenants associés à ce service ont été notifiés.'
                    : 'Service désactivé avec succès. Il n\'est plus visible pour les clients. Tous les intervenants associés à ce service ont été notifiés.';

                return response()->json([
                    'message' => $message,
                    'status' => $service->status,
                    'effects' => [
                        'visible_to_clients' => $newStatus === 'active',
                        'can_receive_new_requests' => $newStatus === 'active',
                        'ongoing_missions_affected' => false,
                        'intervenants_affected' => false
                    ]
                ]);
            } else {
                // Si la colonne n'existe pas encore, retourner une erreur explicite
                return response()->json([
                    'error' => 'La colonne status n\'existe pas dans la table service. Veuillez exécuter la migration.',
                    'message' => 'Migration requise: php artisan migrate'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur toggleServiceStatus: ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du changement de statut du service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compter le nombre de services actifs d'un intervenant
     * 
     * @param Intervenant $intervenant
     * @return int
     */
    private function countActiveServices($intervenant)
    {
        return $intervenant->services()
            ->wherePivot('status', 'active')
            ->count();
    }

    /**
     * Gérer la réactivation d'un service avec la limite de 2 services actifs
     * - Remet les demandes à 'demmande' pour les intervenants actifs qui ont moins de 2 services actifs
     * - Si un intervenant a déjà 2 services actifs ET a ce service avec status='active', 
     *   on le met à 'desactive' avec notification
     * - Si un intervenant a déjà 2 services actifs ET a ce service avec status='desactive',
     *   le service reste à 'desactive' avec notification
     * - Ne touche pas aux demandes qui sont à 'desactive' pour d'autres raisons (suspension intervenant)
     * 
     * @return array Liste des IDs des intervenants exclus (qui ont déjà 2 services actifs)
     */
    private function handleServiceReactivation($service)
    {
        $excludedIntervenantIds = [];
        
        try {
            // Récupérer TOUS les intervenants ACTIFS qui ont ce service (peu importe le statut)
            // Car un intervenant peut avoir ce service avec status='active' mais avoir déjà 2 autres services actifs
            $intervenantsAvecService = Intervenant::with(['utilisateur', 'services'])
                ->where('is_active', true) // Uniquement les intervenants actifs
                ->whereHas('services', function($q) use ($service) {
                    $q->where('service_id', $service->id);
                })
                ->get();

            foreach ($intervenantsAvecService as $intervenant) {
                // Rafraîchir les relations pour avoir les données à jour
                $intervenant->refresh();
                $intervenant->load('services');
                
                // Trouver le statut actuel de ce service pour cet intervenant
                $servicePivot = $intervenant->services->firstWhere('id', $service->id);
                $currentStatus = $servicePivot ? $servicePivot->pivot->status : null;
                
                // Compter les services actifs (en excluant ce service pour avoir le vrai nombre)
                $servicesActifsCount = $intervenant->services()
                    ->wherePivot('status', 'active')
                    ->where('service.id', '!=', $service->id) // Exclure ce service du comptage
                    ->count();
                
                // Si l'intervenant a déjà 2 services actifs (en excluant ce service)
                if ($servicesActifsCount >= 2) {
                    // Ajouter cet intervenant à la liste des exclus (ne pas lui envoyer l'email de réactivation)
                    $excludedIntervenantIds[] = $intervenant->id;
                    
                    // Si le service était 'active', on doit le mettre à 'desactive'
                    // Si le service était 'desactive' ou 'demmande', on le met à 'desactive'
                    if ($currentStatus === 'active' || $currentStatus === 'demmande') {
                        // Mettre à 'desactive' car l'intervenant a déjà 2 autres services actifs
                        $intervenant->services()->updateExistingPivot($service->id, [
                            'status' => 'desactive',
                            'updated_at' => now()
                        ]);
                        
                        Log::info("Service mis à 'desactive' car l'intervenant a déjà 2 services actifs", [
                            'intervenant_id' => $intervenant->id,
                            'service_id' => $service->id,
                            'services_actifs_autres' => $servicesActifsCount,
                            'ancien_statut' => $currentStatus
                        ]);
                    }
                    
                    // Envoyer une notification de refus de demande (pas d'email de réactivation du service)
                    $servicesActifs = $intervenant->services()
                        ->wherePivot('status', 'active')
                        ->where('service.id', '!=', $service->id) // Exclure ce service de la liste
                        ->pluck('nom_service')
                        ->toArray();

                    if ($intervenant->utilisateur && $intervenant->utilisateur->email) {
                        try {
                            $messageReactivation = "Votre demande pour le service '{$service->nom_service}' ne peut pas être acceptée car vous êtes déjà admis à 2 services (" . implode(', ', $servicesActifs) . "). Cette demande a été automatiquement refusée.";
                            Mail::to($intervenant->utilisateur->email)->send(
                                new ServiceRequestRejected(
                                    $intervenant->utilisateur,
                                    $messageReactivation
                                )
                            );
                            
                            Log::info("Notification de refus envoyée à l'intervenant (déjà 2 services actifs)", [
                                'intervenant_id' => $intervenant->id,
                                'service_id' => $service->id,
                                'services_actifs' => $servicesActifs,
                                'ancien_statut' => $currentStatus
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Erreur lors de l'envoi de notification de refus", [
                                'intervenant_id' => $intervenant->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    // L'intervenant a moins de 2 services actifs (en excluant ce service)
                    // On peut remettre la demande à 'demmande' si elle était à 'desactive'
                    if ($currentStatus === 'desactive') {
                        $intervenant->services()->updateExistingPivot($service->id, [
                            'status' => 'demmande',
                            'updated_at' => now()
                        ]);
                        
                        Log::info("Demande remise à 'demmande' pour l'intervenant après réactivation du service", [
                            'intervenant_id' => $intervenant->id,
                            'service_id' => $service->id,
                            'services_actifs_autres' => $servicesActifsCount
                        ]);
                    }
                    // Si le statut était déjà 'active', on le laisse tel quel
                }
            }
            
            return $excludedIntervenantIds;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la gestion de la réactivation du service", [
                'service_id' => $service->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Notify intervenants about service status change
     * Envoie un email à TOUS les intervenants associés à ce service (quel que soit leur statut)
     * Sauf ceux qui sont dans la liste $excludedIntervenantIds (déjà notifiés avec un email de refus)
     * 
     * @param Service $service
     * @param string $newStatus
     * @param array $excludedIntervenantIds Liste des IDs des intervenants à exclure de l'envoi d'email
     */
    private function notifyIntervenantsAboutServiceStatus($service, $newStatus, $excludedIntervenantIds = [])
    {
        try {
            // Récupérer TOUS les intervenants associés à ce service (quel que soit leur statut)
            $intervenants = Intervenant::with(['utilisateur'])
                ->whereHas('services', function($q) use ($service) {
                    $q->where('service_id', $service->id);
                })
                ->get();

            foreach ($intervenants as $intervenant) {
                // Exclure les intervenants qui ont déjà reçu un email de refus (déjà 2 services actifs)
                if (in_array($intervenant->id, $excludedIntervenantIds)) {
                    Log::info("Intervenant exclu de l'email de réactivation (déjà 2 services actifs)", [
                        'intervenant_id' => $intervenant->id,
                        'service_id' => $service->id
                    ]);
                    continue;
                }
                
                // Vérifier que l'intervenant a un utilisateur avec un email
                if ($intervenant->utilisateur && $intervenant->utilisateur->email) {
                    try {
                        if ($newStatus === 'inactive') {
                            // Service désactivé
                            Mail::to($intervenant->utilisateur->email)
                                ->send(new ServiceDeactivated($intervenant, $service));
                        } else {
                            // Service activé
                            Mail::to($intervenant->utilisateur->email)
                                ->send(new ServiceActivated($intervenant, $service));
                        }
                        
                        Log::info("Email sent to intervenant", [
                            'intervenant_id' => $intervenant->id,
                            'email' => $intervenant->utilisateur->email,
                            'service_id' => $service->id,
                            'service_name' => $service->nom_service,
                            'status' => $newStatus
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to send email to intervenant", [
                            'intervenant_id' => $intervenant->id,
                            'email' => $intervenant->utilisateur->email ?? 'N/A',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in notifyIntervenantsAboutServiceStatus", [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get service statistics
     */
    public function getServiceStats($id)
    {
        try {
        $service = \App\Models\Service::findOrFail($id);

            // Calculer les dates une seule fois pour éviter les bugs
            $now = now();
            $lastMonthDate = $now->copy()->subMonth();
            $currentMonth = $now->month;
            $currentYear = $now->year;
            $lastMonth = $lastMonthDate->month;
            $lastMonthYear = $lastMonthDate->year;

            // Total missions
            $totalMissions = \App\Models\Intervention::whereHas('tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->count();
            
            // Missions ce mois
            $missionsCeMois = \App\Models\Intervention::whereHas('tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->whereMonth('date_intervention', $currentMonth)
              ->whereYear('date_intervention', $currentYear)
              ->count();
            
            // Missions du mois précédent pour calculer le pourcentage
            $missionsMoisPrecedent = \App\Models\Intervention::whereHas('tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->whereMonth('date_intervention', $lastMonth)
              ->whereYear('date_intervention', $lastMonthYear)
              ->count();
            
            $croissanceMissions = $missionsMoisPrecedent > 0 
                ? round((($missionsCeMois - $missionsMoisPrecedent) / $missionsMoisPrecedent) * 100) 
                : 0;
            
            // Revenus totaux
            $revenusTotaux = \App\Models\Facture::whereHas('intervention.tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->sum('ttc');
            
            // Revenus ce mois
            $revenusCeMois = \App\Models\Facture::whereHas('intervention.tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->whereMonth('created_at', $currentMonth)
              ->whereYear('created_at', $currentYear)
              ->sum('ttc');
            
            // Revenus du mois précédent
            $revenusMoisPrecedent = \App\Models\Facture::whereHas('intervention.tache', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->whereMonth('created_at', $lastMonth)
              ->whereYear('created_at', $lastMonthYear)
              ->sum('ttc');
            
            $croissanceRevenus = $revenusMoisPrecedent > 0 
                ? round((($revenusCeMois - $revenusMoisPrecedent) / $revenusMoisPrecedent) * 100) 
                : 0;
            
            // Intervenants ce mois
            $intervenantsCeMois = \App\Models\Intervenant::whereHas('services', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->where('is_active', true)
              ->whereMonth('created_at', $currentMonth)
              ->whereYear('created_at', $currentYear)
              ->count();
            
            $intervenantsMoisPrecedent = \App\Models\Intervenant::whereHas('services', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })->where('is_active', true)
              ->whereMonth('created_at', $lastMonth)
              ->whereYear('created_at', $lastMonthYear)
              ->count();
            
            $croissanceIntervenants = $intervenantsMoisPrecedent > 0 
                ? round((($intervenantsCeMois - $intervenantsMoisPrecedent) / $intervenantsMoisPrecedent) * 100) 
                : 0;

            // Total intervenants actifs pour ce service
            $totalIntervenants = \App\Models\Intervenant::whereHas('services', function($q) use ($service) {
                $q->where('service_id', $service->id)
                  ->where('intervenant_service.status', 'active');
            })->where('is_active', true)
              ->count();

        // Calcul de la note moyenne
        $evaluations = \App\Models\Evaluation::whereHas('intervention.tache', function($q) use ($service) {
            $q->where('service_id', $service->id);
        })->where('type_auteur', 'client')->get();

            $noteMoyenne = $evaluations->count() > 0 ? round($evaluations->avg('note'), 1) : 0;

            // Missions par mois (tous les 12 mois de l'année en cours)
            $missionsParMois = [];
            $moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            
            // Compter les missions pour chaque mois de l'année en cours
            for ($month = 1; $month <= 12; $month++) {
                $count = \App\Models\Intervention::whereHas('tache', function($q) use ($service) {
                    $q->where('service_id', $service->id);
                })->whereMonth('date_intervention', $month)
                  ->whereYear('date_intervention', $currentYear)
                  ->count();
                
                $missionsParMois[] = [
                    'mois' => $moisNoms[$month - 1],
                    'count' => $count
                ];
            }

            // Top intervenants (top 5 par nombre de missions)
            $topIntervenants = \App\Models\Intervenant::whereHas('services', function($q) use ($service) {
                $q->where('service_id', $service->id);
            })
            ->withCount(['interventions' => function($q) use ($service) {
                $q->whereHas('tache', function($tq) use ($service) {
                    $tq->where('service_id', $service->id);
                });
            }])
            ->orderBy('interventions_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($intervenant) use ($service) {
                // Calculer la note moyenne de l'intervenant pour ce service
                $evaluations = \App\Models\Evaluation::whereHas('intervention', function($q) use ($intervenant, $service) {
                    $q->where('intervenant_id', $intervenant->id)
                      ->whereHas('tache', function($tq) use ($service) {
                          $tq->where('service_id', $service->id);
                      });
                })->where('type_auteur', 'client')->get();
                
                $note = $evaluations->count() > 0 ? round($evaluations->avg('note'), 1) : 0;
                
                return [
                    'id' => $intervenant->id,
                    'nom' => $intervenant->nom,
                    'prenom' => $intervenant->prenom,
                    'missions' => $intervenant->interventions_count,
                    'note' => $note
                ];
            });

            // Revenus par tâche
            $revenusParTache = \App\Models\Tache::where('service_id', $service->id)
                ->withCount(['interventions'])
                ->get()
                ->map(function($tache) {
                    $revenus = \App\Models\Facture::whereHas('intervention', function($q) use ($tache) {
                        $q->where('tache_id', $tache->id);
                    })->sum('ttc');
                    
                    return [
                        'id' => $tache->id,
                        'nom' => $tache->nom,
                        'revenus' => round($revenus, 2),
                        'missions' => $tache->interventions_count
                    ];
                })
                ->sortByDesc('revenus')
                ->take(3)
                ->values();

            // Déterminer la couleur du service (même logique que getServices)
            $serviceColor = '#808080'; // Couleur par défaut
            if ($service->nom_service === 'Jardinage') {
                $serviceColor = '#92B08B';
            } elseif ($service->nom_service === 'Ménage') {
                $serviceColor = '#5B7C99';
            }
            
            $stats = [
                'service' => [
                    'id' => $service->id,
                    'nom_service' => $service->nom_service ?? 'Service',
                    'couleur' => $serviceColor
                ],
                'totalIntervenants' => $totalIntervenants ?? 0,
                'croissanceIntervenants' => $croissanceIntervenants ?? 0,
                'totalMissions' => $totalMissions ?? 0,
                'croissanceMissions' => $croissanceMissions ?? 0,
                'noteMoyenne' => $noteMoyenne ?? 0,
                'revenusTotaux' => round($revenusTotaux ?? 0, 2),
                'croissanceRevenus' => $croissanceRevenus ?? 0,
                'missionsParMois' => $missionsParMois ?? [],
                'topIntervenants' => $topIntervenants ?? [],
                'revenusParTache' => $revenusParTache ?? []
            ];

        return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Erreur getServiceStats: ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du chargement des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all complaints/reclamations
     */
    public function getReclamations(Request $request)
    {
        $query = Reclamation::query();

        // Filter by archived status (default: show only non-archived)
        if ($request->has('archived')) {
            if ($request->archived === 'true' || $request->archived === '1') {
                $query->where('archived', true);
            } else {
                $query->where('archived', false);
            }
        } else {
            // By default, show only non-archived reclamations
            $query->where('archived', false);
        }

        // Filters
        if ($request->has('statut') && $request->statut !== 'all' && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }
        if ($request->has('priorite') && $request->priorite !== 'all' && $request->priorite !== '') {
            $query->where('priorite', $request->priorite);
        }
        
        // Filtre par date (dateDebut et dateFin)
        if ($request->has('dateDebut') && $request->dateDebut !== '') {
            $query->whereDate('created_at', '>=', $request->dateDebut);
        }
        if ($request->has('dateFin') && $request->dateFin !== '') {
            $query->whereDate('created_at', '<=', $request->dateFin);
        }

        // Exclude specific status
        if ($request->has('exclude_status') && $request->exclude_status !== '') {
            $query->where('statut', '!=', $request->exclude_status);
        }

        // Pagination robuste
        $perPage = (int) $request->input('per_page', 5);
        $perPage = $perPage > 0 ? $perPage : 5;
        $page = (int) $request->input('page', 1);
        $page = $page > 0 ? $page : 1;

        $reclamations = $query
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
        
        // Charger les interventions liées aux réclamations avec leurs relations
        $interventionIds = $reclamations->pluck('intervention_id')->filter()->unique();
        $linkedInterventions = Intervention::with([
            'client.utilisateur:id,nom,prenom,email',
            'intervenant.utilisateur:id,nom,prenom,email',
            'tache.service:id,nom_service',
            'evaluations.critaire:id,nom_critaire', // Charger les évaluations avec leurs critères
            'commentaires' // Charger les commentaires
        ])
        ->whereIn('id', $interventionIds)
        ->get()
        ->keyBy('id');
        
        // Collecter tous les IDs de clients et intervenants depuis les interventions et les réclamations
        $clientIds = collect();
        $intervenantIds = collect();
        
        // Depuis les interventions
        foreach ($linkedInterventions as $intervention) {
            if ($intervention->client_id) {
                $clientIds->push($intervention->client_id);
            }
            if ($intervention->intervenant_id) {
                $intervenantIds->push($intervention->intervenant_id);
            }
        }
        
        // Depuis les réclamations (signale_par ET concernant) en normalisant la casse
        $clientIds = $clientIds->merge(
            $reclamations
                ->filter(fn ($rec) => strtolower($rec->signale_par_type) === 'client')
                ->pluck('signale_par_id')
        );
        $intervenantIds = $intervenantIds->merge(
            $reclamations
                ->filter(fn ($rec) => strtolower($rec->signale_par_type) === 'intervenant')
                ->pluck('signale_par_id')
        );
        
        // Collecter aussi les IDs depuis concernant_id et concernant_type
        $clientIds = $clientIds->merge(
            $reclamations->where('concernant_type', 'Client')->pluck('concernant_id')->filter()
        );
        $intervenantIds = $intervenantIds->merge(
            $reclamations->where('concernant_type', 'Intervenant')->pluck('concernant_id')->filter()
        );
        
        // Charger tous les clients avec leurs utilisateurs
        $clients = Client::with('utilisateur:id,nom,prenom,email')
            ->whereIn('id', $clientIds->filter()->unique())
            ->get()
            ->keyBy('id');
        
        // Charger tous les intervenants avec leurs utilisateurs
        $intervenants = Intervenant::with('utilisateur:id,nom,prenom,email')
            ->whereIn('id', $intervenantIds->filter()->unique())
            ->get()
            ->keyBy('id');
        
        // Précharger toutes les évaluations pour les intervenants
        $allInterventionIdsForEvals = $intervenants->pluck('id')->map(function($id) {
            return Intervention::where('intervenant_id', $id)->pluck('id');
        })->flatten()->unique()->filter();
        
        $evaluationsByIntervenant = [];
        
        if ($allInterventionIdsForEvals->isNotEmpty()) {
            $evaluations = DB::table('evaluation')
                ->join('intervention', 'evaluation.intervention_id', '=', 'intervention.id')
                ->select(
                    'intervention.intervenant_id',
                    'evaluation.intervention_id',
                    'evaluation.note'
                )
                ->whereIn('evaluation.intervention_id', $allInterventionIdsForEvals)
                ->where('evaluation.type_auteur', 'client')
                ->get()
                ->groupBy('intervenant_id');
            
            foreach ($evaluations as $intervenantId => $evals) {
                $groupedByIntervention = $evals->groupBy('intervention_id');
                $notesParIntervention = [];
                foreach ($groupedByIntervention as $interventionId => $interventionEvals) {
                    $notesParIntervention[] = round($interventionEvals->avg('note'), 1);
                }
                
                $evaluationsByIntervenant[$intervenantId] = [
                    'note' => count($notesParIntervention) > 0 
                        ? round(array_sum($notesParIntervention) / count($notesParIntervention), 1) 
                        : 0,
                    'nbAvis' => count($notesParIntervention)
                ];
            }
        }
        
        // Mapper les réclamations avec les données préchargées
        // IMPORTANT: concernant_id et concernant_type sont maintenant dérivés de l'intervention
        $reclamationsData = $reclamations->getCollection()->map(function ($reclamation) use ($clients, $intervenants, $evaluationsByIntervenant, $linkedInterventions) {
            $signaleParName = 'N/A';
            $concernantName = 'N/A';
            $signaleParId = $reclamation->signale_par_id;
            // Normaliser la casse pour fiabiliser les comparaisons
            $signaleParType = $reclamation->signale_par_type
                ? ucfirst(strtolower($reclamation->signale_par_type))
                : null;
            $concernantId = null;
            $concernantType = null;
            $signaleParNote = null;
            $signaleParNbAvis = null;
            $concernantNote = null;
            $concernantNbAvis = null;
            $interventionData = null;

            // Charger les données de l'intervention si elle existe
            if ($reclamation->intervention_id && isset($linkedInterventions[$reclamation->intervention_id])) {
                $intervention = $linkedInterventions[$reclamation->intervention_id];
                
                // Récupérer les évaluations du client et de l'intervenant
                $clientEvaluations = $intervention->evaluations->where('type_auteur', 'client');
                $intervenantEvaluations = $intervention->evaluations->where('type_auteur', 'intervenant');
                
                // Calculer la note moyenne du client
                $clientAverageNote = $clientEvaluations->count() > 0 
                    ? round($clientEvaluations->avg('note'), 1) 
                    : null;
                
                // Calculer la note moyenne de l'intervenant
                $intervenantAverageNote = $intervenantEvaluations->count() > 0 
                    ? round($intervenantEvaluations->avg('note'), 1) 
                    : null;
                
                // Récupérer les commentaires
                $clientComment = $intervention->commentaires->where('type_auteur', 'client')->first();
                $intervenantComment = $intervention->commentaires->where('type_auteur', 'intervenant')->first();
                
                // Construire les données de l'intervention avec les évaluations et commentaires
                $interventionData = [
                    'id' => $intervention->id,
                    'service' => $intervention->tache->service->nom_service ?? 'N/A',
                    'date' => $intervention->date_intervention,
                    'status' => $intervention->status,
                    'address' => $intervention->address,
                    'ville' => $intervention->ville,
                    'evaluations' => [
                        'client' => [
                            'average_note' => $clientAverageNote,
                            'count' => $clientEvaluations->count(),
                            'details' => $clientEvaluations->map(function($eval) {
                                return [
                                    'id' => $eval->id,
                                    'note' => $eval->note,
                                    'critere' => $eval->critaire->nom_critaire ?? 'N/A',
                                ];
                            })->values(),
                            'comment' => $clientComment ? $clientComment->commentaire : null,
                        ],
                        'intervenant' => [
                            'average_note' => $intervenantAverageNote,
                            'count' => $intervenantEvaluations->count(),
                            'details' => $intervenantEvaluations->map(function($eval) {
                                return [
                                    'id' => $eval->id,
                                    'note' => $eval->note,
                                    'critere' => $eval->critaire->nom_critaire ?? 'N/A',
                                ];
                            })->values(),
                            'comment' => $intervenantComment ? $intervenantComment->commentaire : null,
                        ],
                    ],
                ];
                
                // Dériver concernant_id et concernant_type depuis l'intervention
                // Si signale_par est un Client, alors concernant est l'Intervenant de l'intervention
                // Si signale_par est un Intervenant, alors concernant est le Client de l'intervention
                if ($signaleParType === 'Client') {
                    $concernantId = $intervention->intervenant_id;
                    $concernantType = 'Intervenant';
                } elseif ($signaleParType === 'Intervenant') {
                    $concernantId = $intervention->client_id;
                    $concernantType = 'Client';
                }
            } else {
                // Si pas d'intervention, utiliser les valeurs stockées (pour compatibilité)
                $concernantId = $reclamation->concernant_id;
                $concernantType = $reclamation->concernant_type;
            }

            // Normaliser la casse du concernant pour fiabiliser l'affichage
            $concernantType = $concernantType
                ? ucfirst(strtolower($concernantType))
                : null;

            // Récupérer les informations de signale_par
            if ($signaleParId && $signaleParType) {
                if ($signaleParType === 'Client' && isset($clients[$signaleParId])) {
                    $client = $clients[$signaleParId];
                    if ($client && $client->utilisateur) {
                        $prenom = $client->utilisateur->prenom ?? '';
                        $nom = $client->utilisateur->nom ?? '';
                        $signaleParName = trim($prenom . ' ' . $nom);
                        if (empty($signaleParName)) {
                            $signaleParName = 'N/A';
                        }
                    }
                } elseif ($signaleParType === 'Intervenant' && isset($intervenants[$signaleParId])) {
                    $intervenant = $intervenants[$signaleParId];
                    if ($intervenant && $intervenant->utilisateur) {
                        $prenom = $intervenant->utilisateur->prenom ?? '';
                        $nom = $intervenant->utilisateur->nom ?? '';
                        $signaleParName = trim($prenom . ' ' . $nom);
                        if (empty($signaleParName)) {
                            $signaleParName = 'N/A';
                        }
                        
                        // Utiliser les données préchargées
                        if (isset($evaluationsByIntervenant[$signaleParId])) {
                            $signaleParNote = $evaluationsByIntervenant[$signaleParId]['note'];
                            $signaleParNbAvis = $evaluationsByIntervenant[$signaleParId]['nbAvis'];
                        } else {
                            $signaleParNote = 0;
                            $signaleParNbAvis = 0;
                        }
                    }
                }
            }

            // Récupérer les informations de concernant (dérivé de l'intervention)
            if ($concernantId && $concernantType) {
                $concernantTypeNormalized = $concernantType ? strtolower($concernantType) : null;
                if ($concernantTypeNormalized === 'client' && isset($clients[$concernantId])) {
                    $client = $clients[$concernantId];
                    if ($client && $client->utilisateur) {
                        $prenom = $client->utilisateur->prenom ?? '';
                        $nom = $client->utilisateur->nom ?? '';
                        $concernantName = trim($prenom . ' ' . $nom);
                        if (empty($concernantName)) {
                            $concernantName = 'N/A';
                        }
                    }
                } elseif ($concernantTypeNormalized === 'intervenant' && isset($intervenants[$concernantId])) {
                    $intervenant = $intervenants[$concernantId];
                    if ($intervenant && $intervenant->utilisateur) {
                        $prenom = $intervenant->utilisateur->prenom ?? '';
                        $nom = $intervenant->utilisateur->nom ?? '';
                        $concernantName = trim($prenom . ' ' . $nom);
                        if (empty($concernantName)) {
                            $concernantName = 'N/A';
                        }
                        
                        // Utiliser les données préchargées
                        if (isset($evaluationsByIntervenant[$concernantId])) {
                            $concernantNote = $evaluationsByIntervenant[$concernantId]['note'];
                            $concernantNbAvis = $evaluationsByIntervenant[$concernantId]['nbAvis'];
                        } else {
                            $concernantNote = 0;
                            $concernantNbAvis = 0;
                        }
                    }
                }
            }

            return [
                'id' => $reclamation->id,
                'signalePar' => $signaleParName,
                'signaleParId' => $signaleParId,
                'signaleParType' => $signaleParType,
                'signaleParNote' => $signaleParNote,
                'signaleParNbAvis' => $signaleParNbAvis,
                'concernant' => $concernantName,
                'concernantId' => $concernantId,
                'concernantType' => $concernantType,
                'concernantNote' => $concernantNote,
                'concernantNbAvis' => $concernantNbAvis,
                'type' => $signaleParType ?? $reclamation->signale_par_type,
                'raison' => $reclamation->raison,
                'message' => $reclamation->message,
                'priorite' => $reclamation->priorite,
                'statut' => $reclamation->statut,
                'date' => $reclamation->created_at ? $reclamation->created_at->toDateTimeString() : now()->toDateTimeString(),
                'notes' => $reclamation->notes_admin,
                'archived' => $reclamation->archived ?? false,
                'intervention_id' => $reclamation->intervention_id,
                'intervention' => $interventionData,
            ];
        });

        return response()->json([
            'data' => $reclamationsData->values(),
            'pagination' => [
                'current_page' => $reclamations->currentPage(),
                'per_page' => $reclamations->perPage(),
                'total' => $reclamations->total(),
                'last_page' => $reclamations->lastPage(),
                'from' => $reclamations->firstItem() ?? 0,
                'to' => $reclamations->lastItem() ?? 0
            ]
        ]);
    }

    /**
     * Handle complaint action
     */
    public function handleReclamation(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:reply,mark,archive,unarchive', // Added unarchive action
            'notes' => 'nullable|string',
            'statut' => 'nullable|in:en_cours,en_attente,resolu' // For mark action
        ]);

        $reclamation = Reclamation::with('intervention.client.utilisateur', 'intervention.intervenant.utilisateur')->findOrFail($id);
        $oldStatus = $reclamation->statut;
        $message = '';

        // Normaliser le type signale_par pour la comparaison (peut être 'client' ou 'Client')
        $signaleParTypeNormalized = strtolower($reclamation->signale_par_type ?? '');
        
        // Dériver concernant_id et concernant_type depuis l'intervention
        $concernantId = null;
        $concernantType = null;
        
        if ($reclamation->intervention_id && $reclamation->intervention) {
            $intervention = $reclamation->intervention;
            // Si signale_par est un Client, alors concernant est l'Intervenant de l'intervention
            // Si signale_par est un Intervenant, alors concernant est le Client de l'intervention
            if ($signaleParTypeNormalized === 'client') {
                $concernantId = $intervention->intervenant_id;
                $concernantType = 'intervenant';
            } elseif ($signaleParTypeNormalized === 'intervenant') {
                $concernantId = $intervention->client_id;
                $concernantType = 'client';
            }
        } else {
            // Si pas d'intervention, utiliser les valeurs stockées (pour compatibilité)
            $concernantId = $reclamation->concernant_id;
            $concernantType = $reclamation->concernant_type ? strtolower($reclamation->concernant_type) : null;
        }

        if ($validated['action'] === 'reply') {
            // Action: Repondre - Send email to signale_par and concernant
            // 
            // IMPORTANT: Cette action NE CHANGE PAS le statut de la réclamation
            // Le statut reste inchangé (généralement 'en_attente')
            // L'admin doit utiliser "Marquer" séparément pour changer le statut
            // 
            // L'email contient:
            // - La raison de la réclamation
            // - La priorité (haute/moyenne/basse)
            // - Le statut actuel
            // - Les notes d'administration (si présentes)
            $reclamation->notes_admin = $validated['notes'] ?? $reclamation->notes_admin;
            $reclamation->save();

            // Send emails - DIFFERENT emails for signale_par and concernant
            // Utiliser les relations déjà chargées pour éviter les requêtes redondantes
            $emailsSent = 0;
            $emailsFailed = 0;
            
            // Get signale_par name for concernant email
            // Normaliser le type (peut être 'client' ou 'Client' selon la source)
            $signaleParType = strtolower($reclamation->signale_par_type ?? '');
            $signaleParName = 'N/A';
            $signaleParUtilisateur = null;
            
            if ($reclamation->signale_par_id) {
                if ($signaleParType === 'client') {
                    $signalePar = Client::with('utilisateur')->find($reclamation->signale_par_id);
                    if ($signalePar && $signalePar->utilisateur) {
                        $signaleParUtilisateur = $signalePar->utilisateur;
                        $signaleParName = $signalePar->utilisateur->prenom . ' ' . $signalePar->utilisateur->nom;
                    }
                } elseif ($signaleParType === 'intervenant') {
                    $signalePar = Intervenant::with('utilisateur')->find($reclamation->signale_par_id);
                    if ($signalePar && $signalePar->utilisateur) {
                        $signaleParUtilisateur = $signalePar->utilisateur;
                        $signaleParName = $signalePar->utilisateur->prenom . ' ' . $signalePar->utilisateur->nom;
                    }
                }
            }

            // Send email to signale_par (person who reported) - Standard reply email
            // Utiliser un try-catch séparé pour que l'échec d'un email n'empêche pas l'autre
            if ($signaleParUtilisateur && $signaleParUtilisateur->email) {
                try {
                    // Normaliser le type pour l'email (utiliser la première lettre en majuscule)
                    $recipientType = ucfirst($signaleParType);
                    Mail::to($signaleParUtilisateur->email)->send(
                        new ReclamationReply(
                            $reclamation,
                            $signaleParUtilisateur,
                            $recipientType,
                            $reclamation->notes_admin,
                            $reclamation->priorite,
                            $reclamation->statut,
                            $reclamation->raison
                        )
                    );
                    $emailsSent++;
                    Log::info("Email de réponse envoyé au signale_par ({$recipientType}) ID {$reclamation->signale_par_id}, Email: {$signaleParUtilisateur->email}");
                } catch (\Exception $e) {
                    $emailsFailed++;
                    Log::error("Erreur lors de l'envoi de l'email de réponse au signale_par pour la réclamation ID {$id}", [
                        'signale_par_id' => $reclamation->signale_par_id,
                        'signale_par_type' => $reclamation->signale_par_type,
                        'email' => $signaleParUtilisateur->email ?? 'N/A',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning("Impossible d'envoyer l'email au signale_par pour la réclamation ID {$id}: utilisateur ou email manquant", [
                    'signale_par_id' => $reclamation->signale_par_id,
                    'signale_par_type' => $reclamation->signale_par_type
                ]);
            }

            // Send email to concernant (person concerned) - Warning email with alerts
            // Utiliser concernant_id et concernant_type dérivés de l'intervention
            // Normaliser le type (peut être 'Client' ou 'client' selon la source)
            $concernantTypeNormalized = $concernantType ? strtolower($concernantType) : null;
            
            if ($concernantId && $concernantTypeNormalized) {
                $concernantUtilisateur = null;
                
                // Utiliser les relations déjà chargées si disponibles
                if ($reclamation->intervention) {
                    if ($concernantTypeNormalized === 'client' && $reclamation->intervention->client && $reclamation->intervention->client->utilisateur) {
                        $concernantUtilisateur = $reclamation->intervention->client->utilisateur;
                    } elseif ($concernantTypeNormalized === 'intervenant' && $reclamation->intervention->intervenant && $reclamation->intervention->intervenant->utilisateur) {
                        $concernantUtilisateur = $reclamation->intervention->intervenant->utilisateur;
                    }
                }
                
                // Si les relations ne sont pas chargées, faire une requête
                if (!$concernantUtilisateur) {
                    if ($concernantTypeNormalized === 'client') {
                        $concernant = Client::with('utilisateur')->find($concernantId);
                        if ($concernant && $concernant->utilisateur) {
                            $concernantUtilisateur = $concernant->utilisateur;
                        }
                    } elseif ($concernantTypeNormalized === 'intervenant') {
                        $concernant = Intervenant::with('utilisateur')->find($concernantId);
                        if ($concernant && $concernant->utilisateur) {
                            $concernantUtilisateur = $concernant->utilisateur;
                        }
                    }
                }
                
                if ($concernantUtilisateur && $concernantUtilisateur->email) {
                    try {
                        // Normaliser le type pour l'email (utiliser la première lettre en majuscule)
                        $recipientType = ucfirst($concernantTypeNormalized);
                        Mail::to($concernantUtilisateur->email)->send(
                            new ReclamationConcerned(
                                $reclamation,
                                $concernantUtilisateur,
                                $recipientType,
                                $reclamation->notes_admin,
                                $reclamation->priorite,
                                $reclamation->statut,
                                $reclamation->raison,
                                $signaleParName
                            )
                        );
                        $emailsSent++;
                        Log::info("Email d'avertissement envoyé au concernant ({$recipientType}) ID {$concernantId}, Email: {$concernantUtilisateur->email}");
                    } catch (\Exception $e) {
                        $emailsFailed++;
                        Log::error("Erreur lors de l'envoi de l'email d'avertissement au concernant pour la réclamation ID {$id}", [
                            'concernant_id' => $concernantId,
                            'concernant_type' => $concernantType,
                            'email' => $concernantUtilisateur->email ?? 'N/A',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    Log::warning("Impossible d'envoyer l'email au concernant pour la réclamation ID {$id}: utilisateur ou email manquant", [
                        'concernant_id' => $concernantId,
                        'concernant_type' => $concernantType
                    ]);
                }
            } else {
                Log::info("Pas d'email à envoyer au concernant pour la réclamation ID {$id}: concernant_id ou concernant_type manquant");
            }
            
            // Log résumé
            Log::info("Résumé envoi emails pour réclamation ID {$id} (action: reply): {$emailsSent} envoyé(s), {$emailsFailed} échec(s)");

            $message = 'Réponse envoyée avec succès' . ($emailsSent > 0 ? " ({$emailsSent} email(s) envoyé(s))" : '') . ($emailsFailed > 0 ? " ({$emailsFailed} échec(s))" : '') . '.';
        } elseif ($validated['action'] === 'mark') {
            // Action: Marquer - Update status to en cours/en attente/résolue
            // 
            // Statuts possibles via "Marquer" :
            // - 'en_cours' : Réclamation en cours de traitement actif
            // - 'en_attente' : Remettre la réclamation en attente (retour en arrière)
            // - 'resolu' : Réclamation résolue avec succès (statut final)
            // 
            // Note: Seuls les statuts 'en_cours', 'en_attente' et 'resolu' sont disponibles
            $newStatus = $validated['statut'] ?? $reclamation->statut;
            
            if (isset($validated['statut'])) {
                $reclamation->statut = $validated['statut'];
            }
            if (isset($validated['notes'])) {
                $reclamation->notes_admin = $validated['notes'];
            }
            $reclamation->save();
            
            // Envoyer automatiquement un email si le statut est changé à "resolu"
            if ($newStatus === 'resolu' && $oldStatus !== 'resolu') {
                $emailSent = false;
                $emailError = null;
                
                // Récupérer le nom de signale_par pour l'email
                // Normaliser le type (peut être 'client' ou 'Client' selon la source)
                $signaleParTypeNormalized = strtolower($reclamation->signale_par_type ?? '');
                $signaleParName = 'N/A';
                $signaleParUtilisateur = null;
                $concernantName = null;
                
                // Récupérer signale_par - utiliser les relations déjà chargées si disponibles
                if ($reclamation->signale_par_id) {
                    if ($signaleParTypeNormalized === 'client') {
                        $signalePar = Client::with('utilisateur')->find($reclamation->signale_par_id);
                        if ($signalePar && $signalePar->utilisateur) {
                            $signaleParUtilisateur = $signalePar->utilisateur;
                            $signaleParName = $signalePar->utilisateur->prenom . ' ' . $signalePar->utilisateur->nom;
                        }
                    } elseif ($signaleParTypeNormalized === 'intervenant') {
                        $signalePar = Intervenant::with('utilisateur')->find($reclamation->signale_par_id);
                        if ($signalePar && $signalePar->utilisateur) {
                            $signaleParUtilisateur = $signalePar->utilisateur;
                            $signaleParName = $signalePar->utilisateur->prenom . ' ' . $signalePar->utilisateur->nom;
                        }
                    }
                }
                
                // Récupérer le nom de concernant pour l'affichage dans l'email
                // Utiliser concernant_id et concernant_type dérivés de l'intervention
                $concernantTypeNormalized = $concernantType ? strtolower($concernantType) : null;
                if ($concernantId && $concernantTypeNormalized) {
                    // Utiliser les relations déjà chargées si disponibles
                    if ($reclamation->intervention) {
                        if ($concernantTypeNormalized === 'client' && $reclamation->intervention->client && $reclamation->intervention->client->utilisateur) {
                            $concernantName = $reclamation->intervention->client->utilisateur->prenom . ' ' . $reclamation->intervention->client->utilisateur->nom;
                        } elseif ($concernantTypeNormalized === 'intervenant' && $reclamation->intervention->intervenant && $reclamation->intervention->intervenant->utilisateur) {
                            $concernantName = $reclamation->intervention->intervenant->utilisateur->prenom . ' ' . $reclamation->intervention->intervenant->utilisateur->nom;
                        }
                    }
                    
                    // Si les relations ne sont pas chargées, faire une requête
                    if (!$concernantName) {
                        if ($concernantTypeNormalized === 'client') {
                            $concernantTemp = Client::with('utilisateur')->find($concernantId);
                            if ($concernantTemp && $concernantTemp->utilisateur) {
                                $concernantName = $concernantTemp->utilisateur->prenom . ' ' . $concernantTemp->utilisateur->nom;
                            }
                        } elseif ($concernantTypeNormalized === 'intervenant') {
                            $concernantTemp = Intervenant::with('utilisateur')->find($concernantId);
                            if ($concernantTemp && $concernantTemp->utilisateur) {
                                $concernantName = $concernantTemp->utilisateur->prenom . ' ' . $concernantTemp->utilisateur->nom;
                            }
                        }
                    }
                }
                
                // Envoyer l'email uniquement à signale_par (la personne qui a signalé la réclamation)
                if ($signaleParUtilisateur && $signaleParUtilisateur->email) {
                    try {
                        // Normaliser le type pour l'email (utiliser la première lettre en majuscule)
                        $recipientType = ucfirst($signaleParTypeNormalized);
                        Mail::to($signaleParUtilisateur->email)->send(
                            new ReclamationResolved(
                                $reclamation,
                                $signaleParUtilisateur,
                                $recipientType,
                                $reclamation->notes_admin,
                                $reclamation->raison,
                                $signaleParName,
                                $concernantName
                            )
                        );
                        $emailSent = true;
                        Log::info("Email de résolution envoyé au signale_par ({$recipientType}) ID {$reclamation->signale_par_id}, Email: {$signaleParUtilisateur->email}");
                    } catch (\Exception $e) {
                        $emailError = $e->getMessage();
                        Log::error("Erreur lors de l'envoi de l'email de résolution pour la réclamation ID {$reclamation->id}", [
                            'signale_par_id' => $reclamation->signale_par_id,
                            'signale_par_type' => $reclamation->signale_par_type,
                            'email' => $signaleParUtilisateur->email ?? 'N/A',
                            'error' => $emailError,
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Ne pas faire échouer la requête si l'email échoue
                    }
                } else {
                    Log::warning("Impossible d'envoyer l'email de résolution pour la réclamation ID {$reclamation->id}: signale_par utilisateur ou email manquant", [
                        'signale_par_id' => $reclamation->signale_par_id,
                        'signale_par_type' => $reclamation->signale_par_type
                    ]);
                }
                
                if ($emailSent) {
                    Log::info("Email de résolution envoyé avec succès pour la réclamation ID {$reclamation->id}");
                } elseif ($emailError) {
                    Log::error("Échec de l'envoi de l'email de résolution pour la réclamation ID {$reclamation->id}: {$emailError}");
                }
            }
            
            $message = 'Réclamation marquée avec succès.';
        } elseif ($validated['action'] === 'archive') {
            // Action: Archiver
            // 
            // L'archivage est INDÉPENDANT du statut
            // Une réclamation peut être archivée avec n'importe quel statut
            // (en_attente, en_cours, resolu)
            // 
            // Effet: archived = true
            // - La réclamation disparaît de la vue "Actives"
            // - Apparaît dans la vue "Archivées"
            // - Peut être récupérée en changeant archived = false
            $reclamation->archived = true;
            $reclamation->save();
            $message = 'Réclamation archivée avec succès.';
        } elseif ($validated['action'] === 'unarchive') {
            // Action: Désarchiver
            // 
            // Permet de récupérer une réclamation archivée
            // Effet: archived = false
            // - La réclamation réapparaît dans la vue "Actives"
            // - Disparaît de la vue "Archivées"
            $reclamation->archived = false;
            $reclamation->save();
            $message = 'Réclamation désarchivée avec succès.';
        }

        $this->logAdminAction('handle_reclamation', 'reclamation', $id, "Réclamation ID {$id} : {$oldStatus} -> {$reclamation->statut}");

        // Invalider le cache des stats pour mettre à jour immédiatement les badges
        Cache::forget('admin_stats');

        return response()->json([
            'message' => $message,
            'reclamation' => $reclamation,
        ]);
    }

    /**
     * Get intervention history
     */
    public function getHistorique(Request $request)
    {
        $query = \App\Models\Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'facture',
            'evaluations' => function($q) {
                $q->where('type_auteur', 'client');
            },
            'informations',
            'intervenant.taches' // Preload for pricing calculation fallback
        ]);

        // Ne pas filtrer par défaut sur les services actifs : on veut un historique complet,
        // y compris les interventions rattachées à des services aujourd'hui désactivés.

        // Filters
        if ($request->has('statut') && $request->statut !== '' && $request->statut !== 'all') {
            // Map frontend status to database status
            $statusMap = [
                'terminé' => 'termine',
                'acceptée' => 'acceptee',
                'refusée' => 'refuse',
                'en attente' => 'en attend',
                'en_attente' => 'en attend'
            ];
            $dbStatus = $statusMap[$request->statut] ?? $request->statut;
            $query->where('status', $dbStatus);
        }

        if ($request->has('service') && $request->service !== '' && $request->service !== 'all') {
            $query->whereHas('tache.service', function($q) use ($request) {
                $q->where('nom_service', $request->service);
            });
        }

        if ($request->has('dateDebut') && $request->dateDebut !== '') {
            $query->where('date_intervention', '>=', $request->dateDebut);
        }

        if ($request->has('dateFin') && $request->dateFin !== '') {
            $query->where('date_intervention', '<=', $request->dateFin);
        }

        // Modified ordering: Sort by creation date (reservation date) descending
        // This ensures newly reserved interventions appear first
        $historique = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        $transformedData = $historique->through(function($intervention) {
            // Note calculation removed

            // Get duration from intervention_information or use default
            $dureeValue = 3.0; // Numeric standard default
            $dureeStr = '3h'; // Display default
            
            // Try to find duration column in DB (duration_hours) first
            if (isset($intervention->duration_hours) && $intervention->duration_hours > 0) {
                 $dureeValue = (float) $intervention->duration_hours;
                 $dureeStr = $dureeValue . 'h';
            } elseif ($intervention->informations && $intervention->informations->count() > 0) {
                // Fallback to informations table
                foreach ($intervention->informations as $info) {
                    $infoName = strtolower($info->nom ?? '');
                    if (stripos($infoName, 'durée') !== false || stripos($infoName, 'duree') !== false || stripos($infoName, 'duration') !== false) {
                        $infoVal = $info->pivot->information ?? null;
                        if ($infoVal) {
                            $dureeStr = $infoVal;
                            // Extract numeric part for calculation
                            if (preg_match('/([\d\.]+)/', $infoVal, $matches)) {
                                $dureeValue = (float) $matches[1];
                            }
                            // Format duration if needed
                            if (is_numeric($dureeStr) && strpos($dureeStr, 'h') === false) {
                                $dureeStr = $dureeStr . 'h';
                            }
                        }
                        break;
                    }
                }
            }

            // Format status for frontend
            $statusMap = [
                'termine' => 'terminé',
                'acceptee' => 'acceptée',
                'refuse' => 'refusée',
                'en attend' => 'en attente'
            ];
            $statut = $statusMap[$intervention->status] ?? $intervention->status;

            // Format amount
            // Logic: Use Invoice amount if available, otherwise calculate from Intervenant Rate and Material Cost
            $montantValue = 0;
            
            if ($intervention->facture && $intervention->facture->ttc) {
                $montantValue = $intervention->facture->ttc;
            } else {
                // Calculate fallback amount
                // 1. Get Base Rate from Intervenant Tache Pivot
                $baseRate = 0;
                if ($intervention->intervenant && $intervention->intervenant->taches) {
                    $tachePivot = $intervention->intervenant->taches->where('id', $intervention->tache_id)->first();
                    if ($tachePivot && $tachePivot->pivot) {
                        $baseRate = (float) ($tachePivot->pivot->prix_tache ?? 0);
                    }
                }
                
                // 2. Get Materials Cost
                $materialsCost = 0;
                if ($intervention->informations) {
                    foreach ($intervention->informations as $info) {
                        if ($info->nom === 'Coût_Matériel' || stripos($info->nom, 'matériel') !== false) {
                             $val = $info->pivot->information ?? '0';
                             $materialsCost = (float) preg_replace('/[^\d\.]/', '', $val);
                             break;
                        }
                    }
                }
                
                // Calculate Total
                $montantValue = ($baseRate * $dureeValue) + $materialsCost;
            }
            
            $montant = number_format((float)$montantValue, 0, ',', ' ');

            return [
                'id' => $intervention->id,
                'client' => $intervention->client && $intervention->client->utilisateur ?
                    ($intervention->client->utilisateur->prenom . ' ' . $intervention->client->utilisateur->nom) : 'N/A',
                'intervenant' => $intervention->intervenant && $intervention->intervenant->utilisateur ?
                    ($intervention->intervenant->utilisateur->prenom . ' ' . $intervention->intervenant->utilisateur->nom) : 'N/A',
                'service' => $intervention->tache && $intervention->tache->service ?
                    $intervention->tache->service->nom_service : 'N/A',
                'tache' => $intervention->tache ? $intervention->tache->nom_tache : 'N/A',
                'date' => $intervention->date_intervention ? \Carbon\Carbon::parse($intervention->date_intervention)->format('Y-m-d') : 'N/A',
                'duree' => $dureeStr,
                'montant' => $montant,
                'statut' => $statut
            ];
        });

        return response()->json($transformedData);
    }

    /**
     * Export intervention history as CSV
     */
    public function exportHistoriqueCSV(Request $request)
    {
        $query = \App\Models\Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'facture',
            'evaluations' => function($q) {
                $q->where('type_auteur', 'client');
            },
            'informations',
            'intervenant.taches'
        ]);

        // Pas de filtre implicite sur services actifs : conserver tout l'historique.

        // Apply same filters as getHistorique
        if ($request->has('statut') && $request->statut !== '' && $request->statut !== 'all') {
            $statusMap = [
                'terminé' => 'termine',
                'acceptée' => 'acceptee',
                'refusée' => 'refuse',
                'en attente' => 'en attend',
                'en_attente' => 'en attend'
            ];
            $dbStatus = $statusMap[$request->statut] ?? $request->statut;
            $query->where('status', $dbStatus);
        }

        if ($request->has('service') && $request->service !== '' && $request->service !== 'all') {
            $query->whereHas('tache.service', function($q) use ($request) {
                $q->where('nom_service', $request->service);
            });
        }

        if ($request->has('dateDebut') && $request->dateDebut !== '') {
            $query->where('date_intervention', '>=', $request->dateDebut);
        }

        if ($request->has('dateFin') && $request->dateFin !== '') {
            $query->where('date_intervention', '<=', $request->dateFin);
        }

        // Sort by created_at desc (same as main list)
        $interventions = $query->orderBy('created_at', 'desc')->get();

        // Generate filename with date and time: historique_interventions_YYYY-MM-DD_HH-MM-SS.csv
        $filename = 'historique_interventions_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($interventions) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Client',
                'Intervenant',
                'Service',
                'Tâche',
                'Date',
                'Durée',
                'Montant (DH)',
                'Statut'
            ], ';');

            // Data rows
            foreach ($interventions as $intervention) {
                // Note calculation removed

                // Duree Logic (Same as above)
                $dureeValue = 3.0;
                $dureeStr = '3h';
                if (isset($intervention->duration_hours) && $intervention->duration_hours > 0) {
                     $dureeValue = (float) $intervention->duration_hours;
                     $dureeStr = $dureeValue . 'h';
                } elseif ($intervention->informations && $intervention->informations->count() > 0) {
                    foreach ($intervention->informations as $info) {
                        $infoName = strtolower($info->nom ?? '');
                        if (stripos($infoName, 'durée') !== false || stripos($infoName, 'duree') !== false || stripos($infoName, 'duration') !== false) {
                            $infoVal = $info->pivot->information ?? null;
                            if ($infoVal) {
                                $dureeStr = $infoVal;
                                if (preg_match('/([\d\.]+)/', $infoVal, $matches)) {
                                    $dureeValue = (float) $matches[1];
                                }
                                if (is_numeric($dureeStr) && strpos($dureeStr, 'h') === false) {
                                    $dureeStr = $dureeStr . 'h';
                                }
                            }
                            break;
                        }
                    }
                }

                $statusMap = [
                    'termine' => 'terminé',
                    'acceptee' => 'acceptée',
                    'refuse' => 'refusée',
                    'en attend' => 'en attente'
                ];
                $statut = $statusMap[$intervention->status] ?? $intervention->status;

                // Montant Logic (Same as above)
                $montantValue = 0;
                if ($intervention->facture && $intervention->facture->ttc) {
                    $montantValue = $intervention->facture->ttc;
                } else {
                    $baseRate = 0;
                    if ($intervention->intervenant && $intervention->intervenant->taches) {
                        $tachePivot = $intervention->intervenant->taches->where('id', $intervention->tache_id)->first();
                        if ($tachePivot && $tachePivot->pivot) {
                            $baseRate = (float) ($tachePivot->pivot->prix_tache ?? 0);
                        }
                    }
                    $materialsCost = 0;
                    if ($intervention->informations) {
                        foreach ($intervention->informations as $info) {
                            if ($info->nom === 'Coût_Matériel' || stripos($info->nom, 'matériel') !== false) {
                                 $val = $info->pivot->information ?? '0';
                                 $materialsCost = (float) preg_replace('/[^\d\.]/', '', $val);
                                 break;
                            }
                        }
                    }
                    $montantValue = ($baseRate * $dureeValue) + $materialsCost;
                }

                $montant = number_format((float)$montantValue, 2, ',', ' ');

                fputcsv($file, [
                    $intervention->id,
                    $intervention->client && $intervention->client->utilisateur ?
                        ($intervention->client->utilisateur->prenom . ' ' . $intervention->client->utilisateur->nom) : 'N/A',
                    $intervention->intervenant && $intervention->intervenant->utilisateur ?
                        ($intervention->intervenant->utilisateur->prenom . ' ' . $intervention->intervenant->utilisateur->nom) : 'N/A',
                    $intervention->tache && $intervention->tache->service ?
                        $intervention->tache->service->nom_service : 'N/A',
                    $intervention->tache ? $intervention->tache->nom_tache : 'N/A',
                    $intervention->date_intervention ? (\Carbon\Carbon::parse($intervention->date_intervention)->format('Y-m-d')) : 'N/A',
                    $dureeStr,
                    $montant,
                    $statut
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export intervention history as PDF
     */
    public function exportHistoriquePDF(Request $request)
    {
        $query = \App\Models\Intervention::with([
            'client.utilisateur',
            'intervenant.utilisateur',
            'tache.service',
            'facture',
            'evaluations' => function($q) {
                $q->where('type_auteur', 'client');
            },
            'informations',
            'intervenant.taches'
        ]);

        // Pas de filtre implicite sur services actifs : conserver tout l'historique.

        // Apply same filters as getHistorique
        if ($request->has('statut') && $request->statut !== '' && $request->statut !== 'all') {
            $statusMap = [
                'terminé' => 'termine',
                'acceptée' => 'acceptee',
                'refusée' => 'refuse',
                'en attente' => 'en attend',
                'en_attente' => 'en attend'
            ];
            $dbStatus = $statusMap[$request->statut] ?? $request->statut;
            $query->where('status', $dbStatus);
        }

        if ($request->has('service') && $request->service !== '' && $request->service !== 'all') {
            $query->whereHas('tache.service', function($q) use ($request) {
                $q->where('nom_service', $request->service);
            });
        }

        if ($request->has('dateDebut') && $request->dateDebut !== '') {
            $query->where('date_intervention', '>=', $request->dateDebut);
        }

        if ($request->has('dateFin') && $request->dateFin !== '') {
            $query->where('date_intervention', '<=', $request->dateFin);
        }

        // Sort by created_at desc
        $interventions = $query->orderBy('created_at', 'desc')->get();

        // Transform data for PDF
        $data = $interventions->map(function($intervention) {
            $note = null;
            if ($intervention->evaluations && $intervention->evaluations->count() > 0) {
                $avgNote = $intervention->evaluations->avg('note');
                $note = $avgNote ? round($avgNote, 0) : null;
            }

            // Duree Logic
            $dureeValue = 3.0;
            $dureeStr = '3h';
            if (isset($intervention->duration_hours) && $intervention->duration_hours > 0) {
                 $dureeValue = (float) $intervention->duration_hours;
                 $dureeStr = $dureeValue . 'h';
            } elseif ($intervention->informations && $intervention->informations->count() > 0) {
                foreach ($intervention->informations as $info) {
                    $infoName = strtolower($info->nom ?? '');
                    if (stripos($infoName, 'durée') !== false || stripos($infoName, 'duree') !== false || stripos($infoName, 'duration') !== false) {
                        $infoVal = $info->pivot->information ?? null;
                        if ($infoVal) {
                            $dureeStr = $infoVal;
                            if (preg_match('/([\d\.]+)/', $infoVal, $matches)) {
                                $dureeValue = (float) $matches[1];
                            }
                            if (is_numeric($dureeStr) && strpos($dureeStr, 'h') === false) {
                                $dureeStr = $dureeStr . 'h';
                            }
                        }
                        break;
                    }
                }
            }

            $statusMap = [
                'termine' => 'terminé',
                'acceptee' => 'acceptée',
                'refuse' => 'refusée',
                'en attend' => 'en attente'
            ];
            $statut = $statusMap[$intervention->status] ?? $intervention->status;

            // Montant Logic
            $montantValue = 0;
            if ($intervention->facture && $intervention->facture->ttc) {
                $montantValue = $intervention->facture->ttc;
            } else {
                $baseRate = 0;
                if ($intervention->intervenant && $intervention->intervenant->taches) {
                    $tachePivot = $intervention->intervenant->taches->where('id', $intervention->tache_id)->first();
                    if ($tachePivot && $tachePivot->pivot) {
                        $baseRate = (float) ($tachePivot->pivot->prix_tache ?? 0);
                    }
                }
                $materialsCost = 0;
                if ($intervention->informations) {
                    foreach ($intervention->informations as $info) {
                        if ($info->nom === 'Coût_Matériel' || stripos($info->nom, 'matériel') !== false) {
                             $val = $info->pivot->information ?? '0';
                             $materialsCost = (float) preg_replace('/[^\d\.]/', '', $val);
                             break;
                        }
                    }
                }
                $montantValue = ($baseRate * $dureeValue) + $materialsCost;
            }

            $montant = number_format((float)$montantValue, 2, ',', ' ');

            return [
                'id' => $intervention->id,
                'client' => $intervention->client && $intervention->client->utilisateur ?
                    ($intervention->client->utilisateur->prenom . ' ' . $intervention->client->utilisateur->nom) : 'N/A',
                'intervenant' => $intervention->intervenant && $intervention->intervenant->utilisateur ?
                    ($intervention->intervenant->utilisateur->prenom . ' ' . $intervention->intervenant->utilisateur->nom) : 'N/A',
                'service' => $intervention->tache && $intervention->tache->service ?
                    $intervention->tache->service->nom_service : 'N/A',
                'tache' => $intervention->tache ? $intervention->tache->nom_tache : 'N/A',
                'date' => $intervention->date_intervention ? \Carbon\Carbon::parse($intervention->date_intervention)->format('Y-m-d') : 'N/A',
                'duree' => $dureeStr,
                'montant' => $montant,
                'note' => $note,
                'statut' => $statut
            ];
        });

        // Generate HTML for PDF
        $html = view('exports.historique-pdf', [
            'interventions' => $data,
            'filters' => [
                'dateDebut' => $request->get('dateDebut', ''),
                'dateFin' => $request->get('dateFin', ''),
                'statut' => $request->get('statut', ''),
                'service' => $request->get('service', '')
            ]
        ])->render();

        // Use DomPDF to generate PDF (package is installed: barryvdh/laravel-dompdf)
        // Generate filename with date and time: historique_interventions_YYYY-MM-DD_HH-MM-SS.pdf
        $pdfFilename = 'historique_interventions_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Use the facade directly - DomPDF is installed
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        
        return $pdf->download($pdfFilename);

    }

    /**
     * Calculate experience in years
     * Calcule l'expérience depuis la création du compte
     */
    private function calculateExperience($createdAt)
    {
        if (!$createdAt) {
            return 'Débutant';
        }

        // S'assurer que createdAt est une instance Carbon
        if (is_string($createdAt)) {
            $createdAt = \Carbon\Carbon::parse($createdAt);
        }

        $years = now()->diffInYears($createdAt, false); // false = valeur absolue
        
        // Si la date est dans le futur (cas improbable)
        if ($years < 0) {
            return 'Débutant';
        }

        return $years > 0 ? $years . ' ans' : 'Débutant';
    }

    /**
     * Format experience from years (decimal) to "X année(s) Y mois" format
     * Formate l'expérience en années décimales vers le format "X année(s) Y mois"
     * Exemple: 2.5 années -> "2 années 6 mois" (2 années + 0.5*12 = 6 mois)
     * Exemple: 1.25 années -> "1 année 3 mois" (1 année + 0.25*12 = 3 mois)
     */
    private function formatExperience($yearsDecimal)
    {
        if ($yearsDecimal === null || $yearsDecimal === '') {
            return 'Expérience non précisée';
        }

        // Convertir en nombre si c'est une chaîne
        $yearsDecimal = (float) $yearsDecimal;
        
        if ($yearsDecimal <= 0) {
            return 'Débutant';
        }

        // Extraire la partie entière (années) et la partie décimale
        $years = floor($yearsDecimal);
        $decimalPart = $yearsDecimal - $years;
        
        // Convertir la partie décimale en mois (0.5 années = 6 mois, 0.25 années = 3 mois, etc.)
        $months = round($decimalPart * 12);

        $result = '';
        
        if ($years > 0) {
            $result .= $years . ' année' . ($years > 1 ? 's' : '');
        }
        
        if ($months > 0) {
            if ($years > 0) {
                $result .= ' ';
            }
            $result .= $months . ' mois';
        }
        
        // Si c'est exactement 0 années et 0 mois (ne devrait pas arriver avec la condition ci-dessus)
        if (empty($result)) {
            return 'Débutant';
        }
        
        return $result;
    }

    /**
     * Calculate waiting time
     * Calcule le temps d'attente depuis la création de la demande
     */
    private function calculateWaitingTime($createdAt)
    {
        if (!$createdAt) {
            return 'Date inconnue';
        }

        // S'assurer que createdAt est une instance Carbon
        if (is_string($createdAt)) {
            $createdAt = \Carbon\Carbon::parse($createdAt);
        }

        // Calculer la différence (toujours positive)
        $days = now()->diffInDays($createdAt, false); // false = valeur absolue
        
        // Si la date est dans le futur (cas improbable mais possible)
        if ($days < 0) {
            return 'Aujourd\'hui';
        }

        if ($days < 1) {
            $hours = now()->diffInHours($createdAt, false);
            if ($hours < 0) {
                return 'Aujourd\'hui';
            }
            return $hours . 'h';
        } elseif ($days < 30) {
            return $days . ' jour' . ($days > 1 ? 's' : '');
        } else {
            $months = now()->diffInMonths($createdAt, false);
            if ($months < 0) {
                return 'Aujourd\'hui';
            }
            return $months . ' mois';
        }
    }

    /**
     * Get all taches (sub-services) for a service
     */
    public function getServiceTaches($serviceId)
    {
        try {
            $service = Service::findOrFail($serviceId);
            $taches = Tache::where('service_id', $serviceId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'service' => [
                    'id' => $service->id,
                    'nom_service' => $service->nom_service,
                ],
                'taches' => $taches->map(function($tache) {
                    return [
                        'id' => $tache->id,
                        'service_id' => $tache->service_id,
                        'nom_tache' => $tache->nom_tache,
                        'description' => $tache->description,
                        'status' => $tache->status,
                        'image_url' => $tache->image_url,
                        'created_at' => $tache->created_at,
                        'updated_at' => $tache->updated_at,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur getServiceTaches: ' . $e->getMessage(), [
                'service_id' => $serviceId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du chargement des sous-services',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new tache (sub-service) for a service
     */
    public function createTache(Request $request, $serviceId)
    {
        try {
            $service = Service::findOrFail($serviceId);

            $validated = $request->validate([
                'nom_tache' => 'required|string|max:150',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:actif,inactif',
                'image_url' => 'nullable|string|max:255',
            ]);

            $tache = new Tache();
            $tache->service_id = $serviceId;
            $tache->nom_tache = $validated['nom_tache'];
            $tache->description = $validated['description'] ?? null;
            $tache->status = $validated['status'] ?? 'actif';
            $tache->image_url = $validated['image_url'] ?? null;
            $tache->save();

            Log::info("Tâche créée", [
                'tache_id' => $tache->id,
                'service_id' => $serviceId,
                'nom_tache' => $tache->nom_tache
            ]);

            return response()->json([
                'message' => 'Sous-service créé avec succès',
                'tache' => [
                    'id' => $tache->id,
                    'service_id' => $tache->service_id,
                    'nom_tache' => $tache->nom_tache,
                    'description' => $tache->description,
                    'status' => $tache->status,
                    'image_url' => $tache->image_url,
                    'created_at' => $tache->created_at,
                    'updated_at' => $tache->updated_at,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur createTache: ' . $e->getMessage(), [
                'service_id' => $serviceId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors de la création du sous-service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a tache (sub-service)
     */
    public function updateTache(Request $request, $tacheId)
    {
        try {
            $tache = Tache::findOrFail($tacheId);

            $validated = $request->validate([
                'nom_tache' => 'sometimes|required|string|max:150',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:actif,inactif',
                'image_url' => 'nullable|string|max:255',
            ]);

            if (isset($validated['nom_tache'])) {
                $tache->nom_tache = $validated['nom_tache'];
            }
            if (isset($validated['description'])) {
                $tache->description = $validated['description'];
            }
            if (isset($validated['status'])) {
                $tache->status = $validated['status'];
            }
            if (isset($validated['image_url'])) {
                $tache->image_url = $validated['image_url'];
            }
            
            $tache->save();

            Log::info("Tâche modifiée", [
                'tache_id' => $tache->id,
                'nom_tache' => $tache->nom_tache
            ]);

            return response()->json([
                'message' => 'Sous-service modifié avec succès',
                'tache' => [
                    'id' => $tache->id,
                    'service_id' => $tache->service_id,
                    'nom_tache' => $tache->nom_tache,
                    'description' => $tache->description,
                    'status' => $tache->status,
                    'image_url' => $tache->image_url,
                    'created_at' => $tache->created_at,
                    'updated_at' => $tache->updated_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur updateTache: ' . $e->getMessage(), [
                'tache_id' => $tacheId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors de la modification du sous-service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a tache (sub-service)
     */
    public function deleteTache($tacheId)
    {
        try {
            $tache = Tache::findOrFail($tacheId);
            
            // Vérifier si la tâche est utilisée dans des interventions
            $interventionsCount = Intervention::where('tache_id', $tacheId)->count();
            
            if ($interventionsCount > 0) {
                return response()->json([
                    'error' => 'Impossible de supprimer ce sous-service',
                    'message' => "Ce sous-service est utilisé dans {$interventionsCount} intervention(s). Veuillez d'abord supprimer ou modifier ces interventions."
                ], 400);
            }

            $tacheNom = $tache->nom_tache;
            $serviceId = $tache->service_id;
            
            $tache->delete();

            Log::info("Tâche supprimée", [
                'tache_id' => $tacheId,
                'nom_tache' => $tacheNom,
                'service_id' => $serviceId
            ]);

            return response()->json([
                'message' => 'Sous-service supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur deleteTache: ' . $e->getMessage(), [
                'tache_id' => $tacheId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors de la suppression du sous-service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatte les disponibilités d'un intervenant en chaîne lisible.
     */
    protected function formatDisponibilitesForIntervenant($intervenant): ?string
    {
        $dispos = $this->getDisponibilitesCollection($intervenant);
        if (!$dispos || $dispos->isEmpty()) {
            return null;
        }

        $dayLabels = [
            'lundi' => 'Lundi',
            'mardi' => 'Mardi',
            'mercredi' => 'Mercredi',
            'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi',
            'samedi' => 'Samedi',
            'dimanche' => 'Dimanche',
        ];

        $segments = $dispos->map(function ($d) use ($dayLabels) {
            $label = $d->date_specific
                ? $d->date_specific
                : ($dayLabels[$d->jours_semaine] ?? ($d->jours_semaine ? ucfirst(substr($d->jours_semaine, 0, 3)) : ''));

            $timeRange = trim(
                ($d->heure_debut ?? '') .
                (!empty($d->heure_fin) ? ' - ' . $d->heure_fin : '')
            );

            return trim($label . (empty($timeRange) ? '' : ' ' . $timeRange));
        })->filter()->implode(' | ');

        return $segments ?: null;
    }

    /**
     * Retourne les disponibilités sous forme de tableau structuré.
     */
    protected function mapDisponibilites($intervenant): array
    {
        $dispos = $this->getDisponibilitesCollection($intervenant);
        if (!$dispos || $dispos->isEmpty()) {
            return [];
        }

        return $dispos->map(function ($d) {
            return [
                'id' => $d->id,
                'type' => $d->type,
                'jour' => $d->jours_semaine,
                'date' => $d->date_specific,
                'heure_debut' => $d->heure_debut,
                'heure_fin' => $d->heure_fin,
            ];
        })->toArray();
    }

    /**
     * Récupère la collection de disponibilités (relation préchargée ou requête).
     */
    protected function getDisponibilitesCollection($intervenant)
    {
        if ($intervenant->relationLoaded('disponibilites')) {
            return $intervenant->disponibilites;
        }

        return $intervenant->disponibilites()->get();
    }

    /**
     * Format API response with standardized pagination structure
     * Clean code approach: Single method to avoid code duplication
     * 
     * @param \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator|array $data
     * @param \Illuminate\Http\Request $request
     * @param int $defaultPerPage Default items per page
     * @return \Illuminate\Http\JsonResponse
     */
    private function formatPaginatedResponse($data, Request $request, $defaultPerPage = 15)
    {
        // If data is already a paginator, use it directly
        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json([
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem() ?? 0,
                    'to' => $data->lastItem() ?? 0
                ]
            ]);
        }

        // Convert collection/array to collection if needed
        $collection = is_array($data) ? collect($data) : $data;
        
        // Get pagination parameters from request
        $perPage = (int) $request->input('per_page', $defaultPerPage);
        $perPage = $perPage > 0 ? $perPage : $defaultPerPage;
        $page = (int) $request->input('page', 1);
        $page = $page > 0 ? $page : 1;

        // Calculate pagination
        $total = $collection->count();
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $from = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $to = min($page * $perPage, $total);

        // Slice the collection for current page
        $paginatedData = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to
            ]
        ]);
    }

    /**
     * Log admin actions for history
     */
    private function logAdminAction($action, $entityType, $entityId, $description)
    {
        // For now, just log to Laravel logs
        Log::info("Admin Action: {$action} on {$entityType}:{$entityId} - {$description}");
    }
}