<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\User;
use Livewire\Component;

class Student extends Component
{
    public $student;

    public $courses;

    public $assessments;

    public $assessmentsArray;

    public function mount(User $student)
    {
        $this->student = $student;
        $this->courses = $this->student->coursesAsStudent;
        $this->loadAssessments();
    }

    protected function loadAssessments()
    {
        $courseIds = $this->courses->pluck('id');

        $this->assessments = Assessment::with(['course', 'staff'])
            ->whereIn('course_id', $courseIds)
            ->get();

        $this->assessmentsArray = $this->assessments
            ->map(fn ($assessment) => $assessment->toCalendarEvent())
            ->toArray();
    }

    public function render()
    {
        return view('livewire.student');
    }
}
