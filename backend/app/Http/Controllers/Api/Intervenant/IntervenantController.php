<?php

namespace App\Http\Controllers\Api\Intervenant;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use Illuminate\Http\Request;

class IntervenantController extends Controller
{
    /**
     * Display a listing of intervenants
     */
    public function index(Request $request)
    {
        // Optimiser le chargement : ne charger que les relations nécessaires
        $query = Intervenant::with([
            'utilisateur:id,nom,prenom,address',
            'taches:id,nom_tache,service_id',
            'taches.service:id,nom_service'
        ]);

        // Filtrer les intervenants actifs uniquement si demandé
        if ($request->has('active') && $request->active == 'true') {
            $query->active();
        }

        // Filtrer par tâche spécifique (intervenants pouvant effectuer une tâche)
        if ($request->has('tacheId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('id', $request->tacheId);
            });
        }
        
        // Filtrer par service si spécifié - optimisé
        if ($request->has('serviceId')) {
            $query->whereHas('taches', function ($q) use ($request) {
                $q->where('service_id', $request->serviceId);
            });
        }

        // Sélectionner uniquement les colonnes nécessaires
        $intervenants = $query->select('intervenant.id', 'intervenant.ville', 'intervenant.bio', 'intervenant.is_active')
                              ->get();

        // Calculer la note moyenne et le nombre d'avis pour chaque intervenant
        foreach ($intervenants as $intervenant) {
            $ratingInfo = $intervenant->getRatingInfo();
            $intervenant->average_rating = $ratingInfo['average_rating'];
            $intervenant->review_count = $ratingInfo['review_count'];
        }

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
                'utilisateur:id,nom,prenom,address',
                'taches:id,nom_tache,service_id',
                'taches.service:id,nom_service',
                'interventions',
                'disponibilites'
            ])->find($id);

            if (!$intervenant) {
                return response()->json([
                    'message' => 'Intervenant introuvable'
                ], 404);
            }

            $ratingInfo = $intervenant->getRatingInfo();
            $intervenant->average_rating = $ratingInfo['average_rating'];
            $intervenant->review_count = $ratingInfo['review_count'];

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
    public function disponibilites($id)
    {
        $intervenant = Intervenant::findOrFail($id);
        $disponibilites = $intervenant->disponibilites()
            ->orderBy('dateDebut', 'asc')
            ->get();

        return response()->json($disponibilites);
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
}
