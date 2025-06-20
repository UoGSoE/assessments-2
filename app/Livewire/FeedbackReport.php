<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Component;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

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

    public function exportAsExcel()
    {
        $tempDir = sys_get_temp_dir();
        $fileName = now()->format('Y-m-d').'-assessments.xlsx';
        $filePath = $tempDir.'/'.$fileName;

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($filePath);

        $cells = [
            Cell::fromValue('Deadline'),
            Cell::fromValue('Type'),
            Cell::fromValue('Course'),
            Cell::fromValue('Staff'),
            Cell::fromValue('Feedback Type'),
            Cell::fromValue('Feedback Deadline'),
            Cell::fromValue('Feedback Completed Date'),
            Cell::fromValue('Comment'),
            Cell::fromValue('Office Notified'),
            Cell::fromValue('Created At'),
        ];

        $singleRow = new Row($cells);
        $writer->addRow($singleRow);

        foreach ($this->assessments as $assessment) {
            $cells = [
                Cell::fromValue($assessment->deadline),
                Cell::fromValue($assessment->type),
                Cell::fromValue($assessment->course->code),
                Cell::fromValue($assessment->staff->name),
                Cell::fromValue($assessment->feedback_type),
                Cell::fromValue($assessment->feedback_deadline),
                Cell::fromValue($assessment->feedback_completed_date),
                Cell::fromValue($assessment->comment),
                Cell::fromValue($assessment->office_notified),
                Cell::fromValue($assessment->created_at),
            ];

            $singleRow = new Row($cells);
            $writer->addRow($singleRow);
        }

        $writer->close();

        return response()->download($filePath);
    }
}
