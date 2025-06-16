<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Course;
use App\Models\Assessment;
use Flux\Flux;
use Livewire\Component;

class CreateAssessment extends Component
{
    public $assessment;
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
        'comment' => 'nullable|string'
    ];

    public function mount()
    {
        $this->staff = User::where('is_student', false)->get();
        $this->courses = Course::all();
    }

    public function render()
    {
        return view('livewire.create-assessment');
    }

    public function createAssessment()
    {
        $this->validate();

        $assessment = Assessment::create([
            'type' => $this->assessment_type,
            'feedback_type' => $this->staff_feedback_type,
            'course_id' => $this->course_id,
            'staff_id' => $this->staff_id,
            'deadline' => $this->deadline,
            'feedback_deadline' => $this->feedback_deadline,
            'comment' => $this->comment
        ]);

        //return redirect()->route('assessment.show', $this->assessment->id);
        Flux::toast('Assessment created successfully.');
    }
}
