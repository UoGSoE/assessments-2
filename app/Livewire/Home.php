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
    //public $yearFilter = 'all';

    public function mount()
    {
        $this->user = Auth::user();
        if (Auth::user()->is_admin) {
            $this->adminAssessmentsAsArray();
        } else if (Auth::user()->is_staff) {
            $this->staffAssessmentsAsArray();
        } else {
            $this->studentAssessmentsAsArray();
        }
    }

    public function render()
    {
        return view('livewire.home');
    }
    
    protected function adminAssessmentsAsArray()
    {
        // TODO: Make more efficient by using a single query
        
        if ($this->user->school) {
            $courses = Course::where('school', $this->user->school)->get();
        } else {
            $courses = Course::all();
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
        $this->assessments = Assessment::with('course')->where('staff_id', $this->user->id)->get()->map(function ($assessment) {
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
        //if ($this->yearFilter === 'all') {
            $courses = $this->user->coursesAsStudent()->get();
        //} else {
        //    $courses = $this->user->coursesAsStudent()->where('year', $this->yearFilter)->get();
        //}

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

}