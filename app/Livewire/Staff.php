<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\User;
use Livewire\Component;

class Staff extends Component
{
    public $staff;

    public $courses;

    public $assessments;

    public function mount(User $staff)
    {
        $this->staff = $staff;
        $this->courses = $staff->coursesAsStaff;
        $this->assessments = Assessment::with(['course', 'complaints'])
            ->where('staff_id', $this->staff->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.staff');
    }
}
