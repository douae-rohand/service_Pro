<?php

namespace App\Services;

use App\Models\Intervention;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PDFService
{
    /**
     * Generate an invoice PDF for an intervention.
     *
     * @param Intervention $intervention
     * @return string The relative path to the generated PDF file.
     */
    public function generateInvoice(Intervention $intervention)
    {
        // Load relationships
        $intervention->load([
            'client.utilisateur', 
            'intervenant.utilisateur', 
            'tache.service',
            'materiels'
        ]);

        // 1. Calculate Task Cost
        $duration = $intervention->duration_hours ?? 1;
        
        // Get hourly rate from Intervenant-Tache pivot
        $hourlyRate = 0;
        $intervenantTache = $intervention->intervenant->taches()
            ->where('tache_id', $intervention->tache_id)
            ->first();
            
        if ($intervenantTache && $intervenantTache->pivot) {
            $hourlyRate = $intervenantTache->pivot->prix_tache;
        }

        $taskTotal = $hourlyRate * $duration;

        // 2. Calculate Materials Cost
        $materialsCost = 0;
        $materialsDetails = [];

        // Get prices from Intervenant-Materiel pivot for the used materials
        // We need to look up the price set by THIS intervenant for these materials
        $intervenantMaterials = $intervention->intervenant->materiels()
            ->whereIn('materiel.id', $intervention->materiels->pluck('id'))
            ->get()
            ->keyBy('id');

        foreach ($intervention->materiels as $material) {
            $price = 0;
            if (isset($intervenantMaterials[$material->id])) {
                $price = $intervenantMaterials[$material->id]->pivot->prix_materiel ?? 0;
            }
            
            $materialsDetails[] = [
                'name' => $material->nom_materiel,
                'price' => $price
            ];
            
            $materialsCost += $price;
        }

        $totalTTC = $taskTotal + $materialsCost;

        $data = [
            'intervention' => $intervention,
            'client' => $intervention->client,
            'intervenant' => $intervention->intervenant,
            'tache' => $intervention->tache,
            'service' => $intervention->tache->service ?? null,
            'date' => now()->format('d/m/Y'),
            'invoice_number' => 'INV-' . $intervention->id . '-' . now()->timestamp,
            // Calculation details
            'duration' => $duration,
            'hourly_rate' => $hourlyRate,
            'task_total' => $taskTotal,
            'materials_details' => $materialsDetails,
            'materials_total' => $materialsCost,
            'total_ttc' => $totalTTC
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        $filename = 'invoices/invoice_' . $intervention->id . '_' . Str::random(10) . '.pdf';
        
        // Ensure the directory exists
        if (!Storage::disk('public')->exists('invoices')) {
            Storage::disk('public')->makeDirectory('invoices');
        }

        Storage::disk('public')->put($filename, $pdf->output());

        return [
            'path' => $filename,
            'ttc' => $totalTTC
        ];
    }
}
