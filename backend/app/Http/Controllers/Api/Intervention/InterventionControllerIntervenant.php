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
    private function scopeQueryProperty($query)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            // Fallback to request user if guard fails for some reason
            $user = request()->user();
        }

        if (!$user) {
            return $query;
        }

        // Use isIntervenant() helper from Utilisateur model
        if ($user->isIntervenant()) {
            // User ID in Utilisateur table is same as Intervenant ID
            $query->where('intervenant_id', $user->id);
        } else {
            // Not an intervenant, return empty results for safety
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
        $this->scopeQueryProperty($query);

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
            'fichePayement',
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

        // Fallback: Generate Fiche de Payement if it's missing for eligible interventions
        if (in_array($intervention->status, ['acceptee', 'termine']) && (!$intervention->relationLoaded('fichePayement') || !$intervention->fichePayement)) {
            $pdfService = new \App\Services\PDFService();
            $pdfService->generatePaymentSlip($intervention);
            $intervention->load('fichePayement');
        }

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

        $mailSent = false;

        // Generate Fiche de Payement if status is accepted or finished
        if (isset($data['status']) && in_array($data['status'], ['acceptee', 'termine'])) {
            try {
                $pdfService = new \App\Services\PDFService();
                $pdfService->generatePaymentSlip($intervention);
            } catch (\Exception $e) {
                \Log::error("Failed to generate payment slip for intervention {$intervention->id}: " . $e->getMessage());
            }

            // Send Email to Intervenant if accepted
            if ($data['status'] === 'acceptee') {
                try {
                    $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache.service']);
                    // Send email to Intervenant
                    \Illuminate\Support\Facades\Mail::to($intervention->intervenant->utilisateur->email)->send(new \App\Mail\InterventionAccepted($intervention));
                    $mailSent = true;
                    
                    // Generate and send invoice to Client
                    try {
                        $pdfService = new \App\Services\PDFService();
                        $result = $pdfService->generateInvoice($intervention);
                        
                        // Create or update Facture record
                        $facture = \App\Models\Facture::updateOrCreate(
                            ['intervention_id' => $intervention->id],
                            [
                                'fichier_path' => $result['path'],
                                'ttc' => $result['ttc']
                            ]
                        );
                        
                        // Reload intervention with facture
                        $intervention->load('facture');
                        
                        // Send invoice email to Client
                        \Illuminate\Support\Facades\Mail::to($intervention->client->utilisateur->email)
                            ->send(new \App\Mail\InterventionInvoiceMail($intervention));
                        
                        \Log::info("Invoice email sent to client for intervention {$intervention->id}");
                    } catch (\Exception $invoiceEx) {
                        \Log::error("Failed to generate/send invoice for intervention {$intervention->id}: " . $invoiceEx->getMessage());
                    }
                } catch (\Exception $e) {
                     \Log::error("Failed to send acceptance email for intervention {$intervention->id}: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'message' => 'Intervention mise à jour avec succès',
            'intervention' => $intervention->load(['client.utilisateur', 'intervenant.utilisateur', 'tache', 'fichePayement']),
            'mail_sent' => $mailSent
        ]);
    }

    /**
     * Download the payment slip for an intervention.
     */
    public function downloadSlip($id)
    {
        $intervention = \App\Models\Intervention::with('fichePayement')->findOrFail($id);
        
        // Check ownership
        $user = Auth::guard('sanctum')->user();
        if (!$user || !$user->isIntervenant() || $intervention->intervenant_id != $user->id) {
            abort(403, "Vous n'êtes pas autorisé à accéder à cette fiche.");
        }

        if (!$intervention->fichePayement || !$intervention->fichePayement->fichier_path) {
            // Try to generate it if it doesn't exist
            $pdfService = new \App\Services\PDFService();
            $fiche = $pdfService->generatePaymentSlip($intervention);
        } else {
            $fiche = $intervention->fichePayement;
        }

        $path = $fiche->fichier_path;

        if (!\Storage::disk('public')->exists($path)) {
            abort(404, "Le fichier de la fiche de payement est introuvable.");
        }

        return response()->download(storage_path('app/public/' . $path));
    }
    
    /**
     * Get upcoming interventions
     */
    public function upcoming()
    {
        $query = Intervention::upcoming()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);

        $this->scopeQueryProperty($query);

        return response()->json($query->get());
    }

    /**
     * Get completed interventions
     */
    public function completed()
    {
        $query = Intervention::completed()
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache']);
            
        $this->scopeQueryProperty($query);

        return response()->json($query->orderBy('date_intervention', 'desc')->paginate(15));
    }

    /**
     * Count pending interventions for a specific service
     */
    public function countPendingByService($serviceId)
    {
        $query = Intervention::whereIn('status', ['en attend', 'en attente', 'en_attente'])
            ->whereHas('tache', function($q) use ($serviceId) {
                $q->where('service_id', $serviceId);
            });

        $this->scopeQueryProperty($query);

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Count pending interventions for a specific tache
     */
    public function countPendingByTache($tacheId)
    {
        $query = Intervention::whereIn('status', ['en attend', 'en attente', 'en_attente'])
            ->where('tache_id', $tacheId);

        $this->scopeQueryProperty($query);

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Refuse all pending interventions for a specific service
     */
    public function refusePendingByService($serviceId)
    {
        $query = Intervention::whereIn('status', ['en attend', 'en attente', 'en_attente'])
            ->whereHas('tache', function($q) use ($serviceId) {
                $q->where('service_id', $serviceId);
            });

        $this->scopeQueryProperty($query);

        // Update to 'refuse' (standard in migration)
        $count = $query->update(['status' => 'refuse']);

        return response()->json([
            'message' => "$count interventions refusées avec succès",
            'count' => $count
        ]);
    }

    /**
     * Refuse all pending interventions for a specific tache
     */
    public function refusePendingByTache($tacheId)
    {
        $query = Intervention::whereIn('status', ['en attend', 'en attente', 'en_attente'])
            ->where('tache_id', $tacheId);

        $this->scopeQueryProperty($query);

        // Update to 'refuse'
        $count = $query->update(['status' => 'refuse']);

        return response()->json([
            'message' => "$count interventions refusées avec succès",
            'count' => $count
        ]);
    }
}
