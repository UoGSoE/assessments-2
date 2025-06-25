<?php

namespace App\Livewire;

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
        $fileName = now()->format('Y-m-d').'-assessments.xlsx';
        $filePath = $tempDir.'/'.$fileName;

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

    public function codeToSchool($code) {
        $school = substr($code, 0, 3);
        if ($school == 'MAT') {
            return 'MATH';
        } else if ($school == 'PHA') {
            return 'PHAS';
        } else if ($school == 'CHE') {
            return 'CHEM';
        } else if ($school == 'COMP') {
            return 'COMP';
        } else {
            return $school;
        }
    }

    public function codeToYear($code) {
        if (in_array($this->codeToSchool($code), ['COMP', 'MATH', 'PHAS', 'CHEM'])) {
            $year = substr($code, 4, 1);
        } else {
            $year = substr($code, 3, 1);
        }
        return $year;
    }

    public function importCourses() {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($this->importFile->getRealPath());
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();
                    $cells = [
                        'course_title' => $cells[0]->getValue(),
                        'course_code' => $cells[1]->getValue(),
                        'discipline' => $cells[2]->getValue(),
                        // TODO: add 'is_active' to the Course model
                        'active' => $cells[3]->getValue() == 'Yes' ? true : false,
                    ];
                    if ($cells['course_title'] == 'Course Title') {
                        continue;
                    }
                    $course = Course::where('code', $cells['course_code'])->first();
                    if (!$course) {
                        $course = Course::create(
                            ['title' => $cells['course_title'], 
                            'code' => $cells['course_code'], 
                            'school' => $this->codeToSchool($cells['course_code']),
                            'year' => $this->codeToYear($cells['course_code'])
                        ]);
                    }
                }
            }

            $reader->close();
            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    // TODO: change name to import assessments
    public function import() {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($this->importFile->getRealPath());
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();
                    $cells = [
                        'course_code' => $cells[0]->getValue(),
                        'year' => $cells[1]->getValue(),
                        'type' => $cells[2]->getValue(),
                        'feedback_type' => $cells[3]->getValue(),
                        'name' => $cells[4]->getValue(),
                        'email' => $cells[5]->getValue(),
                        'deadline' => $cells[6]->getValue(),
                        'feedback_deadline' => $cells[7]->getValue(),
                        'feedback_completed_date' => $cells[8]->getValue(),
                        'complaints' => $cells[9]->getValue(),
                        'comment' => $cells[10]->getValue(),
                    ];
                    if ($cells['course_code'] == 'Course') {
                        continue;
                    }
                    $course = Course::where('code', $cells['course_code'])->first();
                    // TODO: Obviously title should not be hardcoded in this way
                    if (!$course) {
                        $course = Course::create(['code' => $cells['course_code'], 'title' => 'Unknown', 'year' => $cells['year']]);
                    }
                    $name = explode(', ', $cells['name']);
                    $staff = User::where('surname', $name[0])->first();
                    if (!$staff) {
                        $staff = User::create([
                            'surname' => $name[0], 
                            'forenames' => $name[1], 
                            'email' => $cells['email'], 
                            'username' => 'abc123xyz',
                            'password' => 'secret',
                        ]);
                    }
                    
                    $deadline = $cells['deadline'] ? \DateTime::createFromFormat('d/m/Y', $cells['deadline'])->format('Y-m-d') : null;
                    $feedbackDeadline = $cells['feedback_deadline'] ? \DateTime::createFromFormat('d/m/Y', $cells['feedback_deadline'])->format('Y-m-d') : null;
                    $feedbackCompletedDate = $cells['feedback_completed_date'] ? \DateTime::createFromFormat('d/m/Y', $cells['feedback_completed_date'])->format('Y-m-d') : null;
                    
                    $assessment = Assessment::create([
                        'course_id' => $course->id,
                        'type' => $cells['type'],
                        'feedback_type' => $cells['feedback_type'],
                        'staff_id' => $staff->id,
                        'deadline' => $deadline,
                        'feedback_deadline' => $feedbackDeadline,
                        'feedback_completed_date' => $feedbackCompletedDate,
                        // TODO: import complaints
                        'complaints' => $cells['complaints'],
                        'comment' => $cells['comment'], 
                    ]);
                }
            }
            
            $reader->close();
            
            $this->showImportModal = false;
            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importStudentCourses() {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($this->importFile->getRealPath());
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();
                    $cells = [
                        'forenames' => $cells[0]->getValue(),
                        'surname' => $cells[1]->getValue(),
                        'username' => $cells[2]->getValue(),
                        'course' => $cells[3]->getValue(),
                    ];
                    if ($cells['forenames'] == 'Forenames') {
                        continue;
                    }
                    $course = Course::where('code', $cells['course'])->first();
                    if (!$course) {
                        // Error
                        continue;
                    }
                    $student = User::where('username', $cells['username'])->first();
                    if (!$student) {
                        // Error
                        continue;
                    }
                    $student->coursesAsStudent()->syncWithoutDetaching([$course->id]);
                }
            }

            $reader->close();
            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

}
