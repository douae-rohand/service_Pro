<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * Tâche planifiée pour terminer automatiquement les interventions passées.
 * S'exécute toutes les minutes.
 */
Schedule::call(function () {
    $now = now();
    // Récupérer les interventions acceptées qui ont une date définie
    $interventions = \App\Models\Intervention::where('status', 'acceptee')
        ->whereNotNull('date_intervention')
        ->get();

    foreach ($interventions as $intervention) {
        try {
            // Calculer l'heure de fin (Date début + Durée)
            // Par défaut 2h si non spécifié (mais devrait l'être)
            $duration = isset($intervention->duration_hours) ? (float)$intervention->duration_hours : 2.0;
            
            // On clone la date pour ne pas modifier l'originale en mémoire avant comparaison
            $endTime = $intervention->date_intervention->copy()->addMinutes($duration * 60);

            if ($now->greaterThan($endTime)) {
                $intervention->update(['status' => 'termine']);
                \Illuminate\Support\Facades\Log::info("Auto-completion: Intervention #{$intervention->id} marquée comme terminée.");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur auto-completion intervention #{$intervention->id}: " . $e->getMessage());
        }
    }
})->everyMinute();

/**
 * Tâche planifiée pour marquer les évaluations comme publiques après 7 jours.
 * S'exécute tous les jours à minuit.
 */
Schedule::command('evaluations:mark-public')
    ->daily()
    ->at('00:00')
    ->timezone('Africa/Casablanca');

