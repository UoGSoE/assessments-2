<?php

namespace App\Importers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StaffCourses
{
    public function process($rows)
    {
        if (count($rows[0]) != 5) {
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

            $validator = Validator::make($row, [
                'forenames' => 'required|min:2',
                'surname' => 'required|min:2',
                'username' => 'required|min:3',
                'email' => 'required|email',
                'course_code' => 'required|min:7|max:8',
            ]);

            if ($validator->fails()) {
                $errors[] = 'Row '.$row['row_number'].': '.$validator->errors()->first();

                continue;
            }

            $course = Course::where('code', $row['course_code'])->first();
            if (! $course) {
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
                'is_student' => false,
            ]);

            $staff->coursesAsStaff()->syncWithoutDetaching([$course->id]);
        }

        return $errors;
    }
}
