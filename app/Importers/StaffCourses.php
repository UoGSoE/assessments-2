<?php

namespace App\Importers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;

class StaffCourses
{
    public function process($rows)
    {
        $errors = [];
        foreach ($rows as $row) {
            $row = [
                'forenames' => $row[0],
                'surname' => $row[1],
                'username' => $row[2],
                'email' => $row[3],
                'course_code' => $row[4],
            ];
            if ($row['forenames'] == 'Forenames') {
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
