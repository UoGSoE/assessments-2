<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

class FeedbackReport extends Component
{
    use WithFileUploads;

    public $searchText = '';

    public $assessments = [];

    public $user;

    #[Url]
    public $school;

    #[Validate('required|file|mimes:xlsx,xls')]
    public $importFile;

    public $showImportModal = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->school ??= $this->user->school ?? 'All schools';

        $query = Assessment::with(['course', 'staff', 'complaints'])->orderBy('deadline', 'desc');

        if ($this->school !== 'All schools') {
            $courseIds = Course::where('school', $this->school)->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }

        $this->assessments = $query->get();
    }

    public function render()
    {
        return view('livewire.feedback-report');
    }

    public function updatedSearchText($value)
    {
        $this->reset('assessments');

        $this->assessments = Assessment::with(['course', 'staff', 'complaints'])
            ->where(function ($query) use ($value) {
                $query->where('type', 'like', "%{$value}%")
                    ->orWhereHas('course', function ($courseQuery) use ($value) {
                        $courseQuery->where('code', 'like', "%{$value}%");
                    })
                    ->orWhereHas('staff', function ($staffQuery) use ($value) {
                        $staffQuery->where('surname', 'like', "%{$value}%")
                            ->orWhere('forenames', 'like', "%{$value}%");
                    });
            })
            ->orderBy('deadline', 'desc')
            ->get();
    }

    public function updatedSchool()
    {
        $this->reset('assessments');
        $query = Assessment::query()->with(['course', 'staff', 'complaints'])->orderBy('deadline', 'desc');

        if ($this->school !== 'All schools') {
            $courses = Course::where('school', $this->school)->get();
            $query->whereIn('course_id', $courses->pluck('id'));
        }

        $this->assessments = $query->get();
    }

    public function exportAsExcel()
    {
        $tempDir = sys_get_temp_dir();
        $fileName = now()->format('Y-m-d').'-assessments.xlsx';
        $filePath = $tempDir.'/'.$fileName;

        $writer = new \OpenSpout\Writer\XLSX\Writer;
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

    public function removeAllStudentCourses()
    {
        $students = User::where('is_staff', false)->where('is_admin', false)->get();

        foreach ($students as $student) {
            $student->coursesAsStudent()->detach();
        }
        Flux::toast('All students\' courses removed successfully.', variant: 'success');
        $this->redirect(route('assessment.index'), navigate: true);
    }

    public function deleteAllData()
    {
        foreach (Complaint::all() as $complaint) {
            $complaint->delete();
        }

        foreach (Assessment::all() as $assessment) {
            $assessment->delete();
        }
        Flux::toast('All assessments and complaints removed successfully.', variant: 'success');
        $this->redirect(route('assessment.index'), navigate: true);
    }
}
