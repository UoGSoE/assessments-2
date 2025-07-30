<?php

namespace App\Importers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;

class StaffCourses
{
    public function process($rows)
    {
        if (strtolower($rows[0][0]) != 'forenames' || count($rows[0]) != 5) {
            $errors[] = 'Incorrect file format - please check the file and try again.';
            return $errors;
        }

        $errors = [];
        foreach ($rows as $index => $row) {
            $row = [
                'row_number' => $index + 1,
                'forenames' => $row[0],
                'surname' => $row[1],
                'username' => $row[2],
                'email' => $row[3],
                'course_code' => $row[4],
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

            if ($row['email'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Email is required';
                continue;
            }

            if ($row['course_code'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course code is required';
                continue;
            }

            $course = Course::where('code', $row['course_code'])->first();
            if (!$course) {
                $errors[] = "Course with code '{$row['course_code']}' not found - please add it to the system first.";
                continue;
            }
            $staff = User::firstOrCreate([
                'username' => $row['username'],
            ], [
                'forenames' => $row['forenames'],
                'surname' => $row['surname'],
                'email' => $row['email'],
                'password' => bcrypt(Str::random(64)),
                'is_staff' => true,
                'is_student' => false
            ]);

            $staff->coursesAsStaff()->syncWithoutDetaching([$course->id]);
        }
        return $errors;
    }
}
