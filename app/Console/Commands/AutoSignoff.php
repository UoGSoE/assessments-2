<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use Illuminate\Console\Command;

class AutoSignoff extends Command
{
    protected $signature = 'assessments:auto-signoff';
    protected $description = 'Automatically sign off any applicable assessments';

    public function handle()
    {
        $unsignedOffAssessments = Assessment::where('feedback_completed_date', null)->get();
        foreach ($unsignedOffAssessments as $assessment) {

            if ($assessment->canBeAutoSignedOff()) {
                $assessment->feedback_completed_date = $assessment->feedback_deadline;
                $assessment->save();
            }
        }
        $this->info("Finished auto-signoff for {$unsignedOffAssessments->count()} assessments.");
    }
}