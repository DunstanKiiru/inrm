<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssessmentRound;

class AssessmentsRollForward extends Command
{
    protected $signature = 'assessments:roll-forward';
    protected $description = 'Advance pending/overdue assessment rounds and set statuses';

    public function handle()
    {
        // Mark overdue rounds as in-progress
        $overdue = AssessmentRound::whereNull('status', 'closed')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update(['status' => 'in-progress']);

        $this->info('Updated overdue rounds to in-progress: ' . $overdue);

        return 0;
    }
}
