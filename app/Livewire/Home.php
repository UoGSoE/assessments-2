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
            return $this->assessmentsAsCalendarEvent($assessment, 'assessment');
        })->toArray();
    }

    protected function staffAssessmentsAsArray()
    {
        $courses = Course::query()->forYear($this->yearFilter)->get();

        $this->assessments = Assessment::with('course')->where('staff_id', $this->user->id)->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return $this->assessmentsAsCalendarEvent($assessment, 'assessment');
        })->toArray();

        $feedbackDeadlines = Assessment::with('course')->where('staff_id', $this->user->id)->where('feedback_completed_date', null)->get()->map(function ($assessment) {
            return $this->assessmentsAsCalendarEvent($assessment, 'feedback');
        })->toArray();

        $this->assessments = array_merge($this->assessments, $feedbackDeadlines);
    }

    protected function studentAssessmentsAsArray()
    {
        $courses = $this->user->coursesAsStudent()->get();

        $this->assessments = Assessment::with('course')->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return $this->assessmentsAsCalendarEvent($assessment, 'assessment');
        })->toArray();
    }

    public function updatedYearFilter()
    {
        $this->dispatch('refresh-calendar');
    }

    protected function assessmentsAsCalendarEvent($assessment, $assessmentType = 'assessment')
    {
        $assessmentArray = [
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
        if ($assessmentType == 'feedback') {
            $assessmentArray['color'] = 'crimson';
            $assessmentArray['textColor'] = 'white';
            $assessmentArray['title'] = 'Feedback Due: '.$assessment->course->code.' - '.$assessment->type;
            $assessmentArray['start'] = $assessment->feedback_deadline->toIso8601String();
            $assessmentArray['end'] = $assessment->feedback_deadline->addHours(1)->toIso8601String();
        }

        return $assessmentArray;
    }
}
