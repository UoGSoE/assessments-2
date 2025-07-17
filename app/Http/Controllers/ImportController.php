<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
class ImportController extends Controller
{
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

    public function importCourses(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());
        $errors = (new Courses())->process($data);
        if (count($errors) > 0) {
            return redirect()->route('import.courses.show')->withErrors($errors)->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route('import.courses.show')->with(['message' => 'File imported successfully']);
        }
    }

    public function importDeadlines(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());
        $errors = (new Assessments())->process($data);

        if (count($errors) > 0) {
            return redirect()->route('import.deadlines.show')->withErrors($errors)->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route('import.deadlines.show')->with(['message' => 'File imported successfully']);
        }
    }
    
    public function importStudentCourses(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());
        $errors = (new StudentCourses())->process($data);
        if (count($errors) > 0) {
            return redirect()->route('import.student-courses.show')->withErrors($errors)->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route('import.student-courses.show')->with(['message' => 'File imported successfully']);
        }
    }

    public function importStaffCourses(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());

        $errors = (new StaffCourses())->process($data);

        if (count($errors) > 0) {
            return redirect()->route('import.staff-courses.show')->withErrors($errors)->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route('import.staff-courses.show')->with(['message' => 'File imported successfully']);
        }
    }

    public function importSubmissionWindows(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());
        $errors = (new SubmissionWindows())->process($data);

        if (count($errors) > 0) {
            return redirect()->route('import.courses.show')->withErrors($errors)->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route('import.courses.show')->with(['message' => 'File imported successfully']);
        }
    }
}
