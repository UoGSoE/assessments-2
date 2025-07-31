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
            ->map(fn ($assessment) => $this->assessmentAsCalendarEvent($assessment))
            ->toArray();
    }

    protected function assessmentAsCalendarEvent(Assessment $assessment)
    {
        return [
            'id' => $assessment->id,
            'title' => $assessment->course->code.' - '.$assessment->type,
            'start' => $assessment->deadline->toIso8601String(),
            'end' => $assessment->deadline->addHours(1)->toIso8601String(),
            'course_code' => $assessment->course->code,
            'course_title' => $assessment->course->title,
            'feedback_due' => $assessment->feedback_deadline->toIso8601String(),
            'discipline' => $assessment->course->discipline,
            'color' => 'steelblue',
            'textColor' => 'white',
            'url' => route('assessment.show', $assessment),
            'year' => $assessment->course->year,
        ];
    }

    public function render()
    {
        return view('livewire.student');
    }
}
