<?php

namespace App\Importers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;

class StudentCourses
{
    public function process($rows): array
    {
        $errors = [];

        if (strtolower($rows[0][0]) != 'forenames' || count($rows[0]) != 4) {
            $errors[] = 'Incorrect file format - please check the file and try again.';
            return $errors;
        }

        foreach ($rows as $index => $row) {
            $row = [
                'row_number' => $index + 1,
                'forenames' => $row[0],
                'surname' => $row[1],
                'username' => $row[2],
                'course' => $row[3],
            ];

            if (strtolower($row['forenames']) == 'forenames') {
                continue;
            }

            if ($row['forenames'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Forenames are required';
                continue;
            }

            if ($row['surname'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Surname is required';
                continue;
            }

            if ($row['username'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': GUID is required';
                continue;
            }

            if ($row['course'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course code is required';
                continue;
            }

            $course = Course::where('code', $row['course'])->first();
            if (!$course) {
                $errors[] = "Course with code '{$row['course']}' not found - please add it to the system first.";
                continue;
            }
            
            $student = User::firstOrCreate([
                'username' => $row['username'],
            ], [
                'forenames' => $row['forenames'],
                'surname' => $row['surname'],
                'email' => $row['username'] . '@student.gla.ac.uk',
                'password' => bcrypt(Str::random(64))
            ]);

            $student->coursesAsStudent()->syncWithoutDetaching([$course->id]);
        }
        return $errors;
    }
}
