<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Assessment;

class Student extends Component
{
    public $student;
    public $courses;
    public $assessments;

    public function mount(User $student)
    {
        $this->student = $student;
        $this->courses = $this->student->coursesAsStudent;
        $this->assessments = Assessment::with(['course', 'staff'])
            ->whereIn('course_id', $this->courses->pluck('id'))
            ->get();
    }

    public function render()
    {
        return view('livewire.student');
    }
}
