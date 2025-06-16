<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Course;
use App\Models\Assessment;
use Flux\Flux;
use Livewire\Component;

class EditAssessment extends Component
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

    public function mount($id)
    {
        $this->assessment = Assessment::find($id);
        $this->assessment_type = $this->assessment->type;
        $this->staff_feedback_type = $this->assessment->feedback_type;
        $this->course_id = $this->assessment->course_id;
        $this->staff_id = $this->assessment->staff_id;
        $this->deadline = $this->assessment->deadline;
        $this->feedback_deadline = $this->assessment->feedback_deadline;
        $this->comment = $this->assessment->comment;
        $this->staff = User::where('is_student', false)->get();
        $this->courses = Course::all();
    }

    public function render()
    {
        return view('livewire.edit-assessment');
    }

    public function updateAssessment()
    {
        $this->validate();

        $this->assessment->update([
            'type' => $this->assessment_type,
            'feedback_type' => $this->staff_feedback_type,
            'course_id' => $this->course_id,
            'staff_id' => $this->staff_id,
            'deadline' => $this->deadline,
            'feedback_deadline' => $this->feedback_deadline,
            'comment' => $this->comment
        ]);

        
        //return redirect()->route('assessment.show', $this->assessment->id);
        Flux::toast('Assessment updated successfully.');
    }
}
