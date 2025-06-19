<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Assessment;
use Livewire\Component;

class StaffList extends Component
{
    public $staff;
    public $searchText = '';

    public function mount()
    {
        $this->staff = User::where('is_student', false)->orderBy('surname')->get();
    }

    public function render()
    {
        return view('livewire.staff-list');
    }

    public function updatedSearchText($value)
    {
        $this->reset('staff');
        $searchTerm = $value;
        $this->staff = User::where('surname', 'like', '%' . $searchTerm . '%')->orWhere('forenames', 'like', '%' . $searchTerm . '%')->get();
    }

    public function isLate(Assessment $assessment)
    {
        return $assessment->feedback_deadline < now() && $assessment->feedback_completed_date === null;
    }

    public function wasLate(Assessment $assessment)
    {
        return $assessment->feedback_deadline < $assessment->feedback_completed_date;
    }

    public function getMissedDeadlines(User $staffMember)
    {
        $missedDeadlines = 0;
        foreach ($staffMember->assessments as $assessment) {
            if ($this->isLate($assessment) || $this->wasLate($assessment)) {
                $missedDeadlines++;
            }
        }
        return $missedDeadlines;
    }
}