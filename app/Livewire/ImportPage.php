<?php

namespace App\Livewire;

use App\Importers\Assessments;
use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Importers\StudentCourses;
use App\Importers\SubmissionWindows;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Ohffs\SimpleSpout\ExcelSheet;
use Livewire\WithFileUploads;

class ImportPage extends Component

{
    use WithFileUploads;

    public $fileType;
    public $title;
    public $description;
    public $formatText;
    public $exampleText;

    #[Validate('required|file|mimes:xlsx,xls')]
    public $importFile;

    public function mount($fileType)
    {
        $allowedFileTypes = ['courses', 'student-courses', 'staff-courses', 'deadlines', 'submission-windows'];
        
        if (!in_array($fileType, $allowedFileTypes)) {
            abort(404);
        }

        $this->fileType = $fileType;

        if ($fileType == 'courses') {
            $this->importCoursesText();
        } else if ($fileType == 'student-courses') {
            $this->importStudentCoursesText();
        } else if ($fileType == 'staff-courses') {
            $this->importStaffCoursesText();
        } else if ($fileType == 'deadlines') {
            $this->importDeadlinesText();
        } else if ($fileType == 'submission-windows') {
            $this->importSubmissionWindowsText();
        }
    }

    public function render()
    {
        return view('livewire.import-page');
    }

    public function chooseImport()
    {
        if ($this->fileType == 'courses') {
            $this->importCourses();
        } else if ($this->fileType == 'student-courses') {
            $this->importStudentCourses();
        } else if ($this->fileType == 'staff-courses') {
            $this->importStaffCourses();
        } else if ($this->fileType == 'deadlines') {
            $this->importDeadlines();
        } else if ($this->fileType == 'submission-windows') {
            $this->importSubmissionWindows();
        }
    }

    public function codeToSchool($code)
    {
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

    public function codeToYear($code)
    {
        if (in_array($this->codeToSchool($code), ['COMP', 'MATH', 'PHAS', 'CHEM'])) {
            $year = substr($code, 4, 1);
        } else {
            $year = substr($code, 3, 1);
        }
        return $year;
    }

    public function importCourses()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $data = (new ExcelSheet)->trimmedImport($this->importFile->getRealPath());
            $importer = (new Courses())->process($data);

            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importDeadlines()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $data = (new ExcelSheet)->trimmedImport($this->importFile->getRealPath());
            $importer = (new Assessments())->process($data);

            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importStudentCourses()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $data = (new ExcelSheet)->trimmedImport($this->importFile->getRealPath());
            $errors = (new StudentCourses())->process($data);

            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importStaffCourses()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $data = (new ExcelSheet)->trimmedImport($this->importFile->getRealPath());

            $errors = (new StaffCourses())->process($data);

            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importSubmissionWindows()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $data = (new ExcelSheet)->trimmedImport($this->importFile->getRealPath());
            $errors = (new SubmissionWindows())->process($data);

            $this->importFile = null;
            Flux::toast('File imported successfully', variant: 'success');
            $this->redirect(route('assessment.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function importCoursesText() {
        $this->title = 'Courses';
        $this->formatText = 'Course Title | Code | Discipline | Active (Yes/No)';
        $this->exampleText = 'Aero Engineering | ENG4037 | Aero | Yes';
    }

    public function importStudentCoursesText() {
        $this->title = 'Student Course Allocations';
        $this->description = 'Please ensure all courses are uploaded to the database first.';
        $this->formatText = 'Forenames | Surname | GUID | Course Code';
        $this->exampleText = 'Jane | Smith | 123456789S | ENG1000';
    }

    public function importStaffCoursesText() {
        $this->title = 'Staff Course Allocations';
        $this->description = 'Please ensure all courses are uploaded to the database first.';
        $this->formatText = 'Forenames | Surname | GUID | Email | Course Code';
        $this->exampleText = 'Claire | Jones | cls2x | claire.jones@example.com | ENG1000';
    }

    public function importDeadlinesText() {
        $this->title = 'Deadlines';
        $this->description = 'This is a tool to help you import deadlines from a spreadsheet. All columns are required, though "comments" can be left blank. Please note: this will only import deadlines, not submission windows. For importing submission windows, please use the Submission Window Import page. If the course code, staff email and assessment type is the same as an existing deadline, then the deadline date will be updated.';
        $this->formatText = 'course code | assessment type | feedback type | staff email | submission deadline | comments';
        $this->exampleText = 'ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:07 | My moodle quiz is great';
    }

    public function importSubmissionWindowsText() {
        $this->title = 'Submission Windows';
        $this->description = 'This is a tool to help you import submission windows from a spreadsheet. All columns are required, though "comments" can be left blank. Please note: this will only import submission windows, not deadlines. For importing deadlines, please use the Deadline Import page. If the course code, staff email and assessment type is the same as an existing submission window, then the submission window dates will be updated.';
        $this->formatText = 'course code | assessment type | feedback type | staff email | submission window from | submission window to | comments';
        $this->exampleText = 'ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:08 | 27/06/2025 16:08 | My moodle quiz is great';
    }

}
