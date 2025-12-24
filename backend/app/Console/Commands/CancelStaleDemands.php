<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelStaleDemands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intervention:cancel-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Annule automatiquement les demandes en attente depuis plus de 48 heures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = now()->subHours(48);

        $staleInterventions = \App\Models\Intervention::where('status', 'en attend')
            ->where('created_at', '<', $limit)
            ->with(['client.utilisateur', 'tache'])
            ->get();

        $count = $staleInterventions->count();

        if ($count === 0) {
            $this->info('Aucune demande obsolète trouvée.');
            return;
        }

        foreach ($staleInterventions as $intervention) {
            try {
                $intervention->update(['status' => 'annule']);

                // Envoyer l'email au client
                if ($intervention->client && $intervention->client->utilisateur) {
                    \Illuminate\Support\Facades\Mail::to($intervention->client->utilisateur->email)
                        ->send(new \App\Mail\DemandAutoCancelled($intervention));
                }

                $this->info("Demande #{$intervention->id} annulée et email envoyé.");
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'annulation de la demande #{$intervention->id} : " . $e->getMessage());
            }
        }

        $this->info("Traitement terminé : {$count} demande(s) annulée(s).");
    }
}
