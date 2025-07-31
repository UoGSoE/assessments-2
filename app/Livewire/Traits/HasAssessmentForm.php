<?php

namespace App\Livewire\Traits;

use App\Models\Course;
use App\Models\User;

trait HasAssessmentForm
{
    public $assessment_type;

    public $staff_feedback_type;

    public $staff;

    public $courses;

    public $course_id;

    public $staff_id;

    public $deadline;

    public $feedback_deadline;

    public $comment;

    protected $rules = [
        'assessment_type' => 'required|string',
        'staff_feedback_type' => 'required|string',
        'course_id' => 'required|exists:courses,id',
        'staff_id' => 'required|exists:users,id',
        'deadline' => 'required|date',
        'feedback_deadline' => 'required|date',
        'comment' => 'nullable|string',
    ];

    protected function loadFormData(): void
    {
        $this->staff = User::staff()->get();
        $this->courses = Course::all();
    }

    protected function getAssessmentData(): array
    {
        return [
            'type' => $this->assessment_type,
            'feedback_type' => $this->staff_feedback_type,
            'course_id' => $this->course_id,
            'staff_id' => $this->staff_id,
            'deadline' => $this->deadline,
            'feedback_deadline' => $this->feedback_deadline,
            'comment' => $this->comment,
        ];
    }

    protected function populateFormFromAssessment($assessment): void
    {
        $this->assessment_type = $assessment->type;
        $this->staff_feedback_type = $assessment->feedback_type;
        $this->course_id = $assessment->course_id;
        $this->staff_id = $assessment->staff_id;
        $this->deadline = $assessment->deadline;
        $this->feedback_deadline = $assessment->feedback_deadline;
        $this->comment = $assessment->comment;
    }
}
