<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedbackReport extends Component
{
    #[Validate('required')]
    public $searchText = '';
    public $assessments = [];

    public function mount()
    {
        $this->assessments = Assessment::with(['course', 'staff', 'complaints'])->get();
    }

    public function updatedSearchText($value)
    {
        $this->reset('assessments');
        $searchTerm = $value;
        $courses = Course::where('code', 'like', '%' . $searchTerm . '%')->get();
        if ($courses->count() > 0) {
            $this->assessments = Assessment::with(['course', 'staff', 'complaints'])->whereIn('course_id', $courses->pluck('id'))->get();
        } else {
            $this->assessments = [];
        }
    }

    public function render()
    {
        return view('livewire.feedback-report');
    }

    public function deleteAllData()
    {
        Complaint::query()->delete();
        Assessment::query()->delete();
        
        return redirect()->route('assessment.index');
    }
}
