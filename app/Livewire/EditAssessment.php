<?php

namespace App\Livewire;

use App\Livewire\Traits\HasAssessmentForm;
use App\Models\Assessment;
use Flux\Flux;
use Livewire\Component;

class EditAssessment extends Component
{
    use HasAssessmentForm;

    public $assessment;

    public function mount($id)
    {
        $this->assessment = Assessment::findOrFail($id);
        $this->populateFormFromAssessment($this->assessment);
        $this->loadFormData();
    }

    public function render()
    {
        return view('livewire.edit-assessment');
    }

    public function updateAssessment()
    {
        $this->validate();

        $this->assessment->update($this->getAssessmentData());

        Flux::toast('Assessment updated successfully.', variant: 'success');
        $this->redirect(route('assessment.show', $this->assessment->id), navigate: true);
    }
}
