<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\User;
use App\Mail\OverdueFeedback;
use App\Mail\ProblematicAssessment;
use App\Models\Complaint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyOfficeOverdueFeedback extends Command
{
    protected $signature = 'assessments:notify-office-overdue-feedback';
    protected $description = 'Notify teaching office about problematic assessments';

    public function handle()
    {
        // TODO: Can this query be optimised?
        $count = 0;
        foreach (Assessment::all() as $assessment) {
            if ($assessment->isProblematic() && !$assessment->office_notified) {
                //Mail::to(config('assessments.office_email'))->send(new ProblematicAssessment($assessment));
                $this->info("Would send email to office with {$assessment->course->code} {{$assessment->type}} (feedback due {$assessment->feedback_deadline->format('d/m/Y')})");
                $assessment->office_notified = true;
                $count++;
            }
        }
        $this->info("Finished checking assessments. {$count} assessments would be sent to office.");
    }
}