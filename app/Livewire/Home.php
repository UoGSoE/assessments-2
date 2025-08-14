<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Home extends Component
{
    public $assessments;

    public $user;

    public $yearFilter = 'all';

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        $this->getAppropriateAssessments();

        return view('livewire.home');
    }

    public function getAppropriateAssessments()
    {
        if (Auth::user()->is_admin) {
            $this->adminAssessmentsAsArray();
        } elseif (Auth::user()->is_staff) {
            $this->staffAssessmentsAsArray();
        } else {
            $this->studentAssessmentsAsArray();
        }
    }

    protected function adminAssessmentsAsArray()
    {
        $courses = Course::query()
            ->when($this->user->school, fn ($query) => $query->where('school', $this->user->school))
            ->forYear($this->yearFilter)->get();

        $this->assessments = Assessment::with('course')->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return $assessment->toCalendarEvent('assessment');
        })->toArray();
    }

    protected function staffAssessmentsAsArray()
    {
        $courses = Course::query()->forYear($this->yearFilter)->get();

        $this->assessments = Assessment::with('course')->where('staff_id', $this->user->id)->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return $assessment->toCalendarEvent('assessment');
        })->toArray();

        $feedbackDeadlines = Assessment::with('course')->where('staff_id', $this->user->id)->where('feedback_completed_date', null)->get()->map(function ($assessment) {
            return $assessment->toCalendarEvent('feedback');
        })->toArray();

        $this->assessments = array_merge($this->assessments, $feedbackDeadlines);
    }

    protected function studentAssessmentsAsArray()
    {
        $courses = $this->user->coursesAsStudent()->get();

        $this->assessments = Assessment::with('course')->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return $assessment->toCalendarEvent('assessment');
        })->toArray();
    }

    public function updatedYearFilter()
    {
        $this->dispatch('refresh-calendar');
    }
}
