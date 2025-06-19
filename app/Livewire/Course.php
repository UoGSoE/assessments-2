<?php

namespace App\Livewire;

use App\Models\Course as ModelsCourse;
use App\Models\User;
use Livewire\Component;
use App\Models\Assessment;
class Course extends Component
{
    public $course;
    public $students;
    public $assessments;
    
    public function mount(ModelsCourse $course)
    {
        $this->course = $course;
        $this->students = $this->course->students;
        $this->assessments = Assessment::with(['staff', 'complaints'])
            ->where('course_id', $this->course->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.course');
    }
}
