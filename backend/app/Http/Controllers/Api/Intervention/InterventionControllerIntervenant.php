<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\PhotoIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utilisateur;

class InterventionControllerIntervenant extends Controller
{
    /**
     * Helper to scope queries to the authenticated user (Intervenant only)
     */
    private function scopeQueryProprety($query)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return $query;
        }

        // Use isIntervenant() helper from Utilisateur model
        if ($user->isIntervenant()) {
            // User ID in Utilisateur table is same as Intervenant ID
            $query->where('intervenant_id', $user->id);
        } else {
            // Not an intervenant
            $query->where('id', -1);
        }
        
        return $query;
    }

    /**
     * Display a listing of interventions
     */
    public function index(Request $request)
    {
        $query = Intervention::with(['client.utilisateur', 'intervenant.utilisateur', 'tache', 'photos']);

        // Secure: Apply User Scope
        $this->scopeQueryProprety($query);

        // Filtrer par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $interventions = $query->orderBy('date_intervention', 'desc')->paginate(15);

        return response()->json($interventions);
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

        $currentUser = Auth::guard('sanctum')->user();
        
        // Explicit Access Check for Intervenant
        // Ensure user is an intervenant AND corresponds to the intervention's intervenant_id
        if (!$currentUser || !$currentUser->isIntervenant() || $intervention->intervenant_id != $currentUser->id) {
             abort(403, 'Accès non autorisé.');
        }

        // Privacy Filter - Check if evaluations are public
        $isPublic = \App\Models\Evaluation::where('intervention_id', $id)
            ->where('is_public', true)
            ->exists();
        
        if (!$isPublic) {
            // Strict Privacy: Hide everything until public (blind date)
            $intervention->setRelation('evaluations', collect([]));
            $intervention->setRelation('commentaires', collect([]));
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
        if (!$user || !$user->isIntervenant() || $intervention->intervenant_id != $user->id) {
             abort(403, "Vous n'êtes pas autorisé à modifier cette intervention.");
        }

        $data = [];

        // Intervenant Logic: Status Update or Reschedule
        if ($request->has('status')) {
            $newStatus = $request->status;
            $allowed = ['acceptee', 'refuse', 'termine'];
            
            if (in_array($newStatus, $allowed)) {
                $data['status'] = $newStatus;
            }
        }
        
        if ($request->has('dateIntervention')) {
             $data['date_intervention'] = $request->dateIntervention;
        }

        if (empty($data)) {
            return response()->json(['message' => 'Aucune modification autorisée ou détectée'], 200);
        }

        $intervention->update($data);

        return response()->json([
            'message' => 'Intervention mise à jour avec succès',
            'intervention' => $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache']),
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
        $query = Intervention::completed()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);
            
        $this->scopeQueryProprety($query);

        return response()->json($query->orderBy('date_intervention', 'desc')->paginate(15));
    }
}
