<?php

namespace App\Livewire;

use App\Importers\Assessments;
use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Importers\StudentCourses;
use App\Importers\SubmissionWindows;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class FeedbackReport extends Component
{
    use WithFileUploads;

    #[Validate('required')]
    public $searchText = '';
    public $assessments = [];

    #[Validate('required|file|mimes:xlsx,xls')]
    public $importFile;

    public $showImportModal = false;

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
        $fileName = now()->format('Y-m-d') . '-assessments.xlsx';
        $filePath = $tempDir . '/' . $fileName;

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($filePath);

        $cells = [
            Cell::fromValue('Course'),
            Cell::fromValue('Level'),
            Cell::fromValue('Assessment Type'),
            Cell::fromValue('Feedback Type'),
            Cell::fromValue('Staff'),
            Cell::fromValue('Staff Email'),
            Cell::fromValue('Submission Deadline'),
            Cell::fromValue('Feedback Deadline'),
            Cell::fromValue('Given'),
            Cell::fromValue('Student Complaints'),
            Cell::fromValue('Comments'),
        ];

        $singleRow = new Row($cells);
        $writer->addRow($singleRow);

        foreach ($this->assessments as $assessment) {
            $cells = [
                Cell::fromValue($assessment->course->code),
                Cell::fromValue($assessment->course->year),
                Cell::fromValue($assessment->type),
                Cell::fromValue($assessment->feedback_type),
                Cell::fromValue($assessment->staff->name),
                Cell::fromValue($assessment->staff->email),
                Cell::fromValue($assessment->deadline->format('d/m/Y')),
                Cell::fromValue($assessment->feedback_deadline->format('d/m/Y')),
                Cell::fromValue($assessment->feedback_completed_date ? $assessment->feedback_completed_date->format('d/m/Y') : ''),
                Cell::fromValue($assessment->complaints->count()),
                Cell::fromValue($assessment->comment),
            ];

            $singleRow = new Row($cells);
            $writer->addRow($singleRow);
        }

        $writer->close();

        return response()->download($filePath);
    }

    
}
