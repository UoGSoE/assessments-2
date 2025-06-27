<?php

namespace App\Importers;

use App\Models\Course;
use App\Models\User;

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
            $student = User::where('username', $row['username'])->first();
            if (!$student) {
                $errors[] = "Student with GUID '{$row['username']}' not found - please add them to the system first.";
                continue;
            }
            $student->coursesAsStudent()->syncWithoutDetaching([$course->id]);
        }
        return [];
    }
}
