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

    public function mount($id)
    {
        $this->student = User::find($id);

        $this->courses = $this->student->coursesAsStudent;
        $this->assessments = Assessment::whereIn('course_id', $this->courses->pluck('id'))->get();
    }

    public function render()
    {
        return view('livewire.student');
    }
}
