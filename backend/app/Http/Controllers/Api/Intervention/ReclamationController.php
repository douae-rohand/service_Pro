<?php

namespace App\Http\Controllers\Api\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Intervention;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReclamationController extends Controller
{
    /**
     * Store a newly created reclamation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'intervention_id' => 'required|exists:intervention,id',
            'raison' => 'required|string|max:255',
            'message' => 'required|string',
            'priorite' => 'required|in:haute,moyenne,basse',
        ]);

        try {
            $user = Auth::user();
            if (!$user || !$user->intervenant) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $intervenant = $user->intervenant;
            $intervention = Intervention::findOrFail($request->intervention_id);

            // Ensure this intervention belongs to the intervenant
            if ($intervention->intervenant_id !== $intervenant->id) {
                return response()->json(['message' => 'Unauthorized access to intervention'], 403);
            }

            // Ensure intervention is completed
            if ($intervention->status !== 'termine') {
                return response()->json(['message' => 'Réclamation uniquement autorisée pour les interventions terminées'], 403);
            }

            $client = $intervention->client;

            if (!$client) {
                return response()->json(['message' => 'Client not found for this intervention'], 404);
            }

            $reclamation = Reclamation::create([
                'signale_par_id' => $intervenant->id,
                'signale_par_type' => 'Intervenant',
                'concernant_id' => $client->id,
                'concernant_type' => 'Client',
                'intervention_id' => $intervention->id,
                'raison' => $request->raison,
                'message' => $request->message,
                'priorite' => $request->priorite,
                'statut' => 'en_attente',
            ]);

            return response()->json([
                'message' => 'Réclamation envoyée avec succès',
                'reclamation' => $reclamation
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing reclamation: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
