<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ResetTestRemindersSeeder extends Seeder
{
    /**
     * Reset test interventions to allow immediate reminder testing.
     * Run this before each test: php artisan db:seed --class=ResetTestRemindersSeeder
     */
    public function run(): void
    {
        $clientEmail = 'elallouche.zakariyae@etu.uae.ac.ma';
        
        // Find the test client
        $client = \App\Models\Client::whereHas('utilisateur', function($q) use ($clientEmail) {
            $q->where('email', $clientEmail);
        })->first();

        if (!$client) {
            $this->command->error("Client not found: {$clientEmail}");
            return;
        }

        // Reset all interventions for this client
        $updated = \App\Models\Intervention::where('client_id', $client->id)
            ->where('status', 'termine')
            ->update([
                'reminder_count' => 0,
                'last_reminder_sent_at' => null
            ]);

        $this->command->info("✅ Reset {$updated} intervention(s) for testing.");
        $this->command->info("You can now run: php artisan app:send-evaluation-reminders");
    }
}
