<?php

namespace App\Livewire;

use App\Models\Assessment as ModelsAssessment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Assessment extends Component
{
    public $assessment;
    public $complaints;
    public $feedback_completed_date;
    
    public function mount(ModelsAssessment $assessment)
    {
        $this->assessment = $assessment->load(['course.students', 'complaints.student']);
        $this->complaints = $this->assessment->complaints;
        $this->feedback_completed_date = $this->assessment->feedback_completed_date;
    }

    public function render()
    {
        return view('livewire.assessment');
    }

    public function deleteAssessment()
    {
        $this->assessment->delete();
        return redirect()->route('assessment.index');
    }

    public function saveCompletedDate()
    {
        $this->validate([
            'feedback_completed_date' => 'required|date',
        ]);
        $this->assessment->feedback_completed_date = $this->feedback_completed_date;
        $this->assessment->save();
        Flux::toast('Feedback completed date saved successfully.');
    }

    public function addComplaint()
    {
        $this->assessment->complaints()->create([
            'student_id' => Auth::user()->id,
            'staff_id' => $this->assessment->staff_id,
            'staff_notified' => false,
        ]);
        $this->complaints = $this->assessment->complaints()->get();
        Flux::toast('Complaint added successfully.');
    }
}
