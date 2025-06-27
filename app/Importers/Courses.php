<?php

namespace App\Importers;

use App\Models\Course;
use App\Models\User;

class Courses
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

    public function process($rows): array
    {
        foreach ($rows as $row) {
            $row = [
                'course_title' => $row[0],
                'course_code' => $row[1],
                'discipline' => $row[2],
                // TODO: add 'is_active' to the Course model
                'active' => $row[3] == 'Yes' ? true : false,
            ];
            if ($row['course_title'] == 'Course Title') {
                continue;
            }
            $course = Course::where('code', $row['course_code'])->first();
            if (!$course) {
                $course = Course::create(
                    [
                        'title' => $row['course_title'],
                        'code' => $row['course_code'],
                        'school' => $this->codeToSchool($row['course_code']),
                        'year' => $this->codeToYear($row['course_code'])
                    ]
                );
            }
        }
        return [];
    }
}
