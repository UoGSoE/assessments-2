<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class Home extends Component
{
    public $assessments;
    public $user;
    public $yearFilter = 'All years';

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        if (Auth::user()->is_admin) {
            $this->adminAssessmentsAsArray();
        } else if (Auth::user()->is_staff) {
            $this->staffAssessmentsAsArray();
        } else {
            $this->studentAssessmentsAsArray();
        }
        return view('livewire.home');
    }
    
    protected function adminAssessmentsAsArray()
    {
        
        if ($this->user->school) {
            $courses = Course::where('school', $this->user->school)->get();
        } else {
            $courses = Course::all();
        }

        if ($this->yearFilter === '1st') {
            $courses = $courses->where('year', 1);
        } else if ($this->yearFilter === '2nd') {
            $courses = $courses->where('year', 2);
        } else if ($this->yearFilter === '3rd') {
            $courses = $courses->where('year', 3);
        } else if ($this->yearFilter === '4th') {
            $courses = $courses->where('year', 4);
        } else if ($this->yearFilter === '5th') {
            $courses = $courses->where('year', 5);
        }

        $this->assessments = Assessment::with('course')->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'title' => $assessment->course->code . ' - ' . $assessment->type,
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
        })->toArray();
    }

    protected function staffAssessmentsAsArray()
    {
        if ($this->yearFilter === 'All years') {
            $courses = Course::all();
        } else if ($this->yearFilter === '1st') {
            $courses = Course::where('year', 1)->get();
        } else if ($this->yearFilter === '2nd') {
            $courses = Course::where('year', 2)->get();
        } else if ($this->yearFilter === '3rd') {
            $courses = Course::where('year', 3)->get();
        } else if ($this->yearFilter === '4th') {
            $courses = Course::where('year', 4)->get();
        } else if ($this->yearFilter === '5th') {
            $courses = Course::where('year', 5)->get();
        }
        
        $this->assessments = Assessment::with('course')->where('staff_id', $this->user->id)->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'title' => $assessment->course->code . ' - ' . $assessment->type,
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
        })->toArray();
        $feedbackDeadlines = Assessment::with('course')->where('staff_id', $this->user->id)->where('feedback_completed_date', null)->get()->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'title' => 'Feedback Due: ' . $assessment->course->code . ' - ' . $assessment->type,
                'start' => $assessment->feedback_deadline->toIso8601String(),
                'end' => $assessment->feedback_deadline->addHours(1)->toIso8601String(),
                'course_code' => $assessment->course->code,
                'course_title' => $assessment->course->title,
                'discipline' => $assessment->course->discipline,
                'color' => 'crimson',
                'textColor' => 'white',
                'url' => route('assessment.show', $assessment),
                'year' => $assessment->course->year,
            ];
        })->toArray();
        $this->assessments = array_merge($this->assessments, $feedbackDeadlines);
    }

    protected function studentAssessmentsAsArray()
    {
        $courses = $this->user->coursesAsStudent()->get();

        $this->assessments = Assessment::with('course')->whereIn('course_id', $courses->pluck('id'))->get()->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'title' => $assessment->course->code . ' - ' . $assessment->type,
                'start' => $assessment->deadline->toIso8601String(),
                'end' => $assessment->deadline->addHours(1)->toIso8601String(),
                'course_code' => $assessment->course->code,
                'course_title' => $assessment->course->title,
                'feedback_due' => $assessment->feedback_deadline->toIso8601String(),
                'discipline' => $assessment->course->discipline,
                'color' => 'whitesmoke',
                'textColor' => 'black',
                'url' => route('assessment.show', $assessment),
                'year' => $assessment->course->year,
            ];
        })->toArray();
    }

    public function updatedYearFilter()
    {
        $this->dispatch('refresh-calendar');
    }

}