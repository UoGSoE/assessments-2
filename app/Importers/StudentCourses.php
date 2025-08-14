<?php

namespace App\Importers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentCourses
{
    public function process($rows): array
    {
        $errors = [];

        if (count($rows[0]) != 4) {
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

            $validator = Validator::make($row, [
                'forenames' => 'required|min:2',
                'surname' => 'required|min:2',
                'username' => 'required|min:3',
                'course' => 'required|min:7|max:8',
            ]);

            if ($validator->fails()) {
                $errors[] = 'Row '.$row['row_number'].': '.$validator->errors()->first();

                continue;
            }

            $course = Course::where('code', $row['course'])->first();
            if (! $course) {
                $errors[] = "Course with code '{$row['course']}' not found - please add it to the system first.";

                continue;
            }

            $student = User::firstOrCreate([
                'username' => $row['username'],
            ], [
                'forenames' => $row['forenames'],
                'surname' => $row['surname'],
                'email' => $row['username'].'@student.gla.ac.uk',
                'password' => bcrypt(Str::random(64)),
            ]);

            $student->coursesAsStudent()->syncWithoutDetaching([$course->id]);
        }

        return $errors;
    }
}
