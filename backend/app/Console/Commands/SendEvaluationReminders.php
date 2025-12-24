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
    protected $signature = 'evaluations:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily evaluation reminders to clients and intervenants for completed interventions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending evaluation reminders...');

        $now = now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $oneDayAgo = $now->copy()->subHours(24);

        // Get completed interventions within the 7-day window
        $interventions = \App\Models\Intervention::where('status', 'termine')
            ->where('updated_at', '>=', $sevenDaysAgo)
            ->where('updated_at', '<=', $now)
            ->with(['client.utilisateur', 'intervenant.utilisateur', 'tache', 'evaluations'])
            ->get();

        $clientRemindersSent = 0;
        $intervenantRemindersSent = 0;

        foreach ($interventions as $intervention) {
            $this->info("Processing Intervention #{$intervention->id} (Completed at: {$intervention->updated_at})");

            // Check if client has evaluated
            $clientHasEvaluated = $intervention->evaluations
                ->where('type_auteur', 'client')
                ->isNotEmpty();

            // Check if intervenant has evaluated
            $intervenantHasEvaluated = $intervention->evaluations
                ->where('type_auteur', 'intervenant')
                ->isNotEmpty();

            // Send reminder to client if they haven't evaluated
            if (!$clientHasEvaluated) {
                $shouldSendClientReminder = false;

                if (is_null($intervention->client_last_reminder_sent_at)) {
                    // Never sent a reminder
                    $shouldSendClientReminder = true;
                } elseif ($intervention->client_last_reminder_sent_at->lt($oneDayAgo)) {
                    // Last reminder was more than 24 hours ago
                    $shouldSendClientReminder = true;
                }

                if ($shouldSendClientReminder && $intervention->client && $intervention->client->utilisateur) {
                    try {
                        $this->info("Sending reminder to client: {$intervention->client->utilisateur->email}");
                        \Illuminate\Support\Facades\Mail::to($intervention->client->utilisateur->email)
                            ->send(new \App\Mail\EvaluationReminderClient($intervention));

                        $intervention->update(['client_last_reminder_sent_at' => $now]);
                        $clientRemindersSent++;
                        $this->info("Reminder sent to client for intervention #{$intervention->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to send reminder to client for intervention #{$intervention->id}: " . $e->getMessage());
                    }
                } else {
                    $this->line("  - Skipping client reminder (Already sent within 24h or missing user)");
                }
            } else {
                $this->line("  - Skipping client reminder (Already evaluated)");
            }

            // Send reminder to intervenant if they haven't evaluated
            if (!$intervenantHasEvaluated) {
                $shouldSendIntervenantReminder = false;

                if (is_null($intervention->intervenant_last_reminder_sent_at)) {
                    // Never sent a reminder
                    $shouldSendIntervenantReminder = true;
                } elseif ($intervention->intervenant_last_reminder_sent_at->lt($oneDayAgo)) {
                    // Last reminder was more than 24 hours ago
                    $shouldSendIntervenantReminder = true;
                }

                if ($shouldSendIntervenantReminder && $intervention->intervenant && $intervention->intervenant->utilisateur) {
                    try {
                        $this->info("Sending reminder to intervenant: {$intervention->intervenant->utilisateur->email}");
                        \Illuminate\Support\Facades\Mail::to($intervention->intervenant->utilisateur->email)
                            ->send(new \App\Mail\EvaluationReminderIntervenant($intervention));

                        $intervention->update(['intervenant_last_reminder_sent_at' => $now]);
                        $intervenantRemindersSent++;
                        $this->info("Reminder sent to intervenant for intervention #{$intervention->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to send reminder to intervenant for intervention #{$intervention->id}: " . $e->getMessage());
                    }
                } else {
                    $this->line("  - Skipping intervenant reminder (Already sent within 24h or missing user)");
                }
            } else {
                $this->line("  - Skipping intervenant reminder (Already evaluated)");
            }
        }

        $this->info("\nCompleted! Sent {$clientRemindersSent} client reminders and {$intervenantRemindersSent} intervenant reminders.");
        
        return Command::SUCCESS;
    }
}
