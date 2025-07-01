<?php

namespace App\Importers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;

class StudentCourses
{
    /**
     * Create a new class instance.
     */

    public function process($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $row = [
                'forenames' => $row[0],
                'surname' => $row[1],
                'username' => $row[2],
                'course' => $row[3],
            ];
            if ($row['forenames'] == 'Forenames') {
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
