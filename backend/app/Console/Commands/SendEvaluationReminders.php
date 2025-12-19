<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendEvaluationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-evaluation-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels d\'évaluation aux clients pour les interventions terminées';

    public function handle()
    {
        $this->info('Starting evaluation reminders check...');

        $interventions = \App\Models\Intervention::where('status', 'termine')
            ->where('date_intervention', '>=', \Carbon\Carbon::now()->subDays(7))
            ->where('reminder_count', '<', 7)
            ->where(function ($query) {
                $query->whereNull('last_reminder_sent_at')
                      ->orWhere('last_reminder_sent_at', '<', \Carbon\Carbon::now()->subHours(24));
            })
            ->whereDoesntHave('evaluation', function($q) {
                $q->where('type_auteur', 'client');
            })
            ->with(['client.utilisateur'])
            ->get();

        $this->info('Found ' . $interventions->count() . ' interventions to remind.');

        foreach ($interventions as $intervention) {
            try {
                if ($intervention->client && $intervention->client->utilisateur) {
                    $user = $intervention->client->utilisateur;
                    
                    // Send Email
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\EvaluationReminderMail($intervention));
                    
                    // Send Notification
                    $user->notify(new \App\Notifications\EvaluationReminderNotification($intervention));

                    // Update intervention
                    $intervention->reminder_count++;
                    $intervention->last_reminder_sent_at = \Carbon\Carbon::now();
                    $intervention->save();
                    
                    $this->info("Reminder sent to {$user->email} for intervention {$intervention->id}");
                }
            } catch (\Exception $e) {
                $this->error("Error sending reminder for intervention {$intervention->id}: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Error sending reminder for intervention {$intervention->id}", ['exception' => $e]);
            }
        }
        
        $this->info('Done.');
    }
}
