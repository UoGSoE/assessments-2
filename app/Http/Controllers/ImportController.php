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
    private $importers = [
        'courses' => [
            'importer' => Courses::class,
            'route' => 'import.courses.show'
        ],
        'deadlines' => [
            'importer' => Assessments::class,
            'route' => 'import.deadlines.show'
        ],
        'student-courses' => [
            'importer' => StudentCourses::class,
            'route' => 'import.student-courses.show'
        ],
        'staff-courses' => [
            'importer' => StaffCourses::class,
            'route' => 'import.staff-courses.show'
        ],
        'submission-windows' => [
            'importer' => SubmissionWindows::class,
            'route' => 'import.submission-windows.show'
        ]
    ];

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

    public function import(Request $request, $type)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ]);

        $config = $this->importers[$type];

        $data = (new ExcelSheet)->trimmedImport($request->importFile->getRealPath());
        $errors = (new $config['importer']())->process($data);

        if (count($errors) > 0) {
            return redirect()->route($config['route'])
                ->withErrors($errors)
                ->with(['message' => 'There were errors importing the file. Rows without errors have been imported.']);
        } else {
            return redirect()->route($config['route'])
                ->with(['message' => 'File imported successfully']);
        }
    }
}
