<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\User;
use App\Mail\OverdueFeedback;
use App\Models\Complaint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyStaffOverdueFeedback extends Command
{
    protected $signature = 'assessments:notify-staff-overdue-feedback';
    protected $description = 'Notify staff members about overdue feedback';

    public function handle()
    {
        $staff = User::where('is_staff', true)->get();
        foreach ($staff as $staffMember) {
            $complaints = Complaint::where('staff_id', $staffMember->id)->where('staff_notified', false)->get();
            if ($complaints->count() > 0) {
                Mail::to($staffMember->email)->send(new OverdueFeedback($complaints));
                $this->info("Email sent to {$staffMember->email} with {$complaints->count()} complaints");
            }
            foreach ($complaints as $complaint) {
                $complaint->staff_notified = true;
            }
        }
        $this->info("Finished checking staff.");
    }
}