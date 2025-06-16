<?php

namespace App\Livewire;

use App\Models\Assessment as ModelsAssessment;
use Flux\Flux;
use Livewire\Component;

class Assessment extends Component
{
    public $assessment;
    public $complaints;
    public $feedback_completed_date;
    
    public function mount($id)
    {
        $this->assessment = ModelsAssessment::find($id);
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
        $this->assessment->feedback_completed_date = $this->feedback_completed_date;
        $this->assessment->save();
        Flux::toast('Feedback completed date saved successfully.');
    }
}
