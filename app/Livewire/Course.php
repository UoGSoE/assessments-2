<?php

namespace App\Livewire;

use App\Models\Course as ModelsCourse;
use Livewire\Component;

class Course extends Component
{
    public $course;

    public $students;

    public $assessments;

    public function mount(ModelsCourse $course)
    {
        $this->course = $course;
        $this->students = $course->students;
        $this->assessments = $course->assessments()->with(['staff', 'complaints'])->get();
    }

    public function render()
    {
        return view('livewire.course');
    }
}
