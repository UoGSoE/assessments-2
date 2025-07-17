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
    public $assessmentsArray;

    public function mount(User $student)
    {
        $this->student = $student;
        $this->courses = $this->student->coursesAsStudent;
        $this->assessments = Assessment::with(['course', 'staff'])
            ->whereIn('course_id', $this->courses->pluck('id'))
            ->get();
        $this->assessmentsArray = Assessment::with('course')->whereIn('course_id', $this->courses->pluck('id'))->get()->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'title' => $assessment->course->code . ' - ' . $assessment->type,
                'start' => $assessment->deadline->toIso8601String(),
                'end' => $assessment->deadline->addHours(1)->toIso8601String(),
                'course_code' => $assessment->course->code,
                'course_title' => $assessment->course->title,
                'feedback_due' => $assessment->feedback_deadline->toIso8601String(),
                'discipline' => $assessment->course->discipline,
                'color' => 'whitesmoke',
                'textColor' => 'black',
                'url' => route('assessment.show', $assessment),
                'year' => $assessment->course->year,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.student');
    }
}
