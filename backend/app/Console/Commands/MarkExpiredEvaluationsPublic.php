<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Intervention;
use App\Models\Evaluation;
use App\Models\Commentaire;
use Carbon\Carbon;

class MarkExpiredEvaluationsPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluations:mark-public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark evaluations and comments as public after 7 days have passed since intervention completion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired evaluation periods...');

        // Get all completed interventions where 7 days have passed
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $interventions = Intervention::where('status', 'termine')
            ->where('updated_at', '<=', $sevenDaysAgo)
            ->get();

        $updatedCount = 0;

        foreach ($interventions as $intervention) {
            // Check if there are any non-public evaluations for this intervention
            $hasNonPublicEvaluations = Evaluation::where('intervention_id', $intervention->id)
                ->where('is_public', false)
                ->exists();

            if ($hasNonPublicEvaluations) {
                // Mark all evaluations for this intervention as public
                Evaluation::where('intervention_id', $intervention->id)
                    ->update(['is_public' => true]);

                // Mark all comments for this intervention as public
                Commentaire::where('intervention_id', $intervention->id)
                    ->update(['is_public' => true]);

                $updatedCount++;
                $this->info("Marked intervention #{$intervention->id} evaluations as public");
            }
        }

        $this->info("Completed! Updated {$updatedCount} interventions.");
        
        return Command::SUCCESS;
    }
}
