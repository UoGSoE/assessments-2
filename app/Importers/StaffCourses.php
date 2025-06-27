<?php

namespace App\Importers;

use App\Models\Course;
use App\Models\User;

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
            $staff = User::where('username', $row['username'])->first();
            if (!$staff) {
                $errors[] = "Staff with GUID '{$row['username']}' not found - please add them to the system first.";
                continue;
            }
            $staff->coursesAsStaff()->syncWithoutDetaching([$course->id]);
        }
        return [];
    }
}
