<?php

namespace App\Importers;

use App\Models\Course;

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
        $errors = [];

        if (strtolower($rows[0][0]) != 'course title' || count($rows[0]) != 4) {
            $errors[] = 'Incorrect file format - please check the file and try again.';
            return $errors;
        }

        foreach ($rows as $index => $row) {
            $row = [
                'row_number' => $index + 1,
                'course_title' => $row[0],
                'course_code' => $row[1],
                'discipline' => $row[2],
                'active' => $row[3]
            ];

            if (strtolower($row['course_title']) == 'course title') {
                continue;
            }

            if ($row['course_title'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course title is required';
                continue;
            }

            if ($row['course_code'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course code is required';
                continue;
            }

            if ($row['discipline'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course discipline is required';
                continue;
            }

            if ($row['active'] != 'Yes' && $row['active'] != 'No') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course active must be Yes or No';
                continue;
            }

            $course = Course::where('code', $row['course_code'])->first();

            if (!$course) {
                $course = Course::create(
                    [
                        'title' => $row['course_title'],
                        'code' => $row['course_code'],
                        'school' => $this->codeToSchool($row['course_code']),
                        'year' => $this->codeToYear($row['course_code']),
                        'is_active' => $row['active'] == 'Yes' ? true : false,
                        'discipline' => $row['discipline']
                    ]
                );
            }
        }
        return $errors;
    }
}
