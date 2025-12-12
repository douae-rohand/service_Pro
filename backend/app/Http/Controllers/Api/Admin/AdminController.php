<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervenant;
use App\Models\Intervention;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        $totalClients = Client::count();
        $totalIntervenants = Intervenant::where('is_active', true)->count();
        $interventionsMois = Intervention::whereMonth('date_intervention', now()->month)
            ->whereYear('date_intervention', now()->year)
            ->count();
        
        // Calcul du taux de croissance
        $clientsLastMonth = Client::whereMonth('created_at', now()->subMonth()->month)->count();
        $clientsGrowth = $clientsLastMonth > 0 
            ? round((($totalClients - $clientsLastMonth) / $clientsLastMonth) * 100) 
            : 0;

        $intervenantsLastMonth = Intervenant::where('is_active', true)
            ->whereMonth('created_at', now()->subMonth()->month)->count();
        $intervenantsGrowth = $intervenantsLastMonth > 0 
            ? round((($totalIntervenants - $intervenantsLastMonth) / $intervenantsLastMonth) * 100) 
            : 0;

        $interventionsLastMonth = Intervention::whereMonth('date_intervention', now()->subMonth()->month)
            ->whereYear('date_intervention', now()->subMonth()->year)
            ->count();
        $interventionsGrowth = $interventionsLastMonth > 0 
            ? round((($interventionsMois - $interventionsLastMonth) / $interventionsLastMonth) * 100) 
            : 0;

        // Calcul de la satisfaction depuis les évaluations des clients
        $evaluations = \App\Models\Evaluation::where('type_auteur', 'client')->get();
        $noteMoyenne = $evaluations->count() > 0 ? $evaluations->avg('note') : 0;
        $satisfaction = $noteMoyenne > 0 ? round(($noteMoyenne / 5) * 100) : 0;
        $satisfactionLabel = $noteMoyenne > 0 ? round($noteMoyenne, 1) . '/5' : '0/5';

        // Calcul des heures totales (estimation: 2h par intervention en moyenne)
        $totalInterventions = Intervention::count();
        $heuresTotal = $totalInterventions * 2; // 2 heures par intervention
        
        // Heures du mois dernier pour calculer la croissance
        $interventionsLastMonthTotal = Intervention::whereMonth('date_intervention', now()->subMonth()->month)
            ->whereYear('date_intervention', now()->subMonth()->year)
            ->count();
        $heuresLastMonth = $interventionsLastMonthTotal * 2;
        
        $heuresGrowth = $heuresLastMonth > 0 
            ? round((($heuresTotal - $heuresLastMonth) / $heuresLastMonth) * 100) 
            : 0;

        // Demandes en attente (intervenants non activés)
        $demandesEnAttente = Intervenant::where('is_active', false)->count();

        return response()->json([
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
            'reclamationsNouvelles' => 0
        ]);
    }

    /**
     * Get all clients with their user info
     */
    public function getClients(Request $request)
    {
        // Charger uniquement les données nécessaires pour la liste
        $query = Client::with(['utilisateur', 'interventions' => function($q) {
            $q->select('id', 'client_id', 'date_intervention');
        }]);

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

        $clients = $query->get()->map(function($client) {
            // Vérifier si l'utilisateur existe
            if (!$client->utilisateur) {
                return null;
            }
            
            $interventions = $client->interventions;
            
            // Calculer rapidement le montant total et le nombre d'avis
            $interventionsIds = $interventions->pluck('id');
            
            // Montant total (requête optimisée)
            $montantTotal = \App\Models\Facture::whereIn('intervention_id', $interventionsIds)
                ->sum('ttc');
            
            // Nombre d'avis (requête optimisée)
            $nbAvis = \App\Models\Evaluation::whereIn('intervention_id', $interventionsIds)
                ->where('type_auteur', 'client')
                ->distinct('intervention_id')
                ->count('intervention_id');
            
            // Dernière intervention
            $derniereIntervention = $interventions->sortByDesc('date_intervention')->first();
            
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
                'dernierService' => $derniereIntervention ? $derniereIntervention->date_intervention : null,
                'avis' => $nbAvis,
                // Les feedbacks seront chargés uniquement dans le modal de détails
                'feedbacks' => []
            ];
        })->filter(); // Filtrer les valeurs null

        return response()->json($clients->values()); // Reset les clés
    }

    /**
     * Get client details with feedbacks (appelé uniquement lors de l'ouverture du modal)
     */
    public function getClientDetails($id)
    {
        $client = Client::with(['utilisateur', 'interventions'])->findOrFail($id);
        
        if (!$client->utilisateur) {
            return response()->json(['error' => 'Client non trouvé'], 404);
        }
        
        $interventions = $client->interventions;
        $interventionsIds = $interventions->pluck('id');
        
        // Calculer le montant total
        $montantTotal = \App\Models\Facture::whereIn('intervention_id', $interventionsIds)->sum('ttc');
        
        // Dernière intervention
        $derniereIntervention = $interventions->sortByDesc('date_intervention')->first();
        
        // Récupérer les feedbacks (uniquement ici, pas dans la liste)
        $feedbacks = [];
        if ($interventionsIds->isNotEmpty()) {
            $evaluations = \App\Models\Evaluation::whereIn('intervention_id', $interventionsIds)
                ->where('type_auteur', 'client')
                ->with('intervention.intervenant.utilisateur', 'intervention.tache.service')
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
     */
    public function getIntervenants(Request $request)
    {
        $query = Intervenant::with(['utilisateur', 'taches', 'services', 'interventions']);

        // Filtrer par statut
        if ($request->has('status')) {
            $isActive = $request->status === 'actif';
            $query->where('is_active', $isActive);
        }

        // Filtrer par service
        if ($request->has('service') && $request->service !== 'tous') {
            $query->whereHas('services', function($q) use ($request) {
                $q->where('nom_service', $request->service);
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

        $intervenants = $query->get()->map(function($intervenant) use ($request) {
            // Vérifier si l'utilisateur existe
            if (!$intervenant->utilisateur) {
                return null;
            }
            
            $interventions = $intervenant->interventions;
            
            // Si un filtre service est appliqué, utiliser ce service, sinon prendre le premier
            $servicePrincipal = null;
            if ($request->has('service') && $request->service !== 'tous') {
                // Chercher le service qui correspond au filtre
                $servicePrincipal = $intervenant->services->firstWhere('nom_service', $request->service);
            }
            
            // Si pas trouvé ou pas de filtre, prendre le premier service
            if (!$servicePrincipal) {
                $servicePrincipal = $intervenant->services->first();
            }
            
            // Calculer la note moyenne et le nombre d'avis depuis les évaluations
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
            
            return [
                'id' => $intervenant->id,
                'nom' => $intervenant->utilisateur->nom ?? '',
                'prenom' => $intervenant->utilisateur->prenom ?? '',
                'email' => $intervenant->utilisateur->email ?? '',
                'telephone' => $intervenant->utilisateur->telephone ?? '',
                'adresse' => $intervenant->address ?? '',
                'ville' => $intervenant->ville ?? '',
                'bio' => $intervenant->bio ?? '',
                'service' => $servicePrincipal ? $servicePrincipal->nom_service : 'Non assigné',
                'serviceId' => $servicePrincipal ? $servicePrincipal->id : null,
                'allServices' => $intervenant->services->pluck('nom_service')->toArray(), // Tous les services
                'note' => $avgNote ? round($avgNote, 1) : 0,
                'nbAvis' => $nbAvis,
                'missions' => $interventions->count(),
                'dateInscription' => $intervenant->created_at->format('Y-m-d'),
                'statut' => $intervenant->is_active ? 'actif' : 'suspendu',
                'taches' => $intervenant->taches->map(function($tache) {
                    return [
                        'id' => $tache->id,
                        'nom' => $tache->nom_tache,
                        'tarif' => $tache->pivot->prix_tache ?? 0,
                        'description' => $tache->description,
                        'duree' => '2-3 heures'
                    ];
                }),
                'disponibilite' => 'Lun-Sam 8h-18h', // À implémenter depuis la table disponibilite
                'avis' => $avisList, // Liste complète des avis
                'repartitionNotes' => $repartitionNotes // Pourcentages par étoile
            ];
        })->filter(); // Filtrer les valeurs null

        return response()->json($intervenants->values()); // Reset les clés
    }

    /**
     * Toggle intervenant status (active/suspended)
     */
    public function toggleIntervenantStatus($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $intervenant->is_active = !$intervenant->is_active;
        $intervenant->save();

        return response()->json([
            'message' => $intervenant->is_active ? 'Intervenant activé' : 'Intervenant suspendu',
            'statut' => $intervenant->is_active ? 'actif' : 'suspendu'
        ]);
    }

}
