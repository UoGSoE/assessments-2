<?php

namespace App\Livewire;

use App\Livewire\Traits\HasAssessmentForm;
use App\Models\Assessment;
use Flux\Flux;
use Livewire\Component;

class CreateAssessment extends Component
{
    use HasAssessmentForm;

    public function mount()
    {
        $this->loadFormData();
    }

    public function render()
    {
        return view('livewire.create-assessment');
    }

    public function createAssessment()
    {
        $this->validate();

        $assessment = Assessment::create($this->getAssessmentData());

        Flux::toast('Assessment created successfully.');
        $this->redirect(route('assessment.show', $assessment->id), navigate: true);
    }
}
