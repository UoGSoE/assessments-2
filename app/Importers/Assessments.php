<?php

namespace App\Importers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;

class Assessments
{
    public function process($rows): array
    {
        $errors = [];

        // TODO: is this a good way to check the file format?
        if (strtolower($rows[0][0]) != 'course code' || count($rows[0]) != 6) {
            $errors[] = 'Incorrect file format - please check the file and try again.';
            return $errors;
        }

        foreach ($rows as $index => $row) {

            $row = [
                'row_number' => $index + 1,
                'course_code' => $row[0],
                'assessment_type' => $row[1],
                'feedback_type' => $row[2],
                'email' => $row[3],
                'deadline' => $row[4],
                'comment' => $row[5],
            ];

            if (strtolower($row['course_code']) == 'course code') {
                continue;
            }

            if ($row['course_code'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Course code is required';
                continue;
            }

            if ($row['assessment_type'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Assessment type is required';
                continue;
            }

            if ($row['feedback_type'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Feedback type is required';
                continue;
            }

            if ($row['email'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Email is required';
                continue;
            }

            if ($row['deadline'] == '') {
                $errors[] = 'Row ' . $row['row_number'] . ': Deadline is required';
                continue;
            }

            $course = Course::where('code', $row['course_code'])->first();
            if (!$course) {
                $errors[] = "Course with code '{$row['course_code']}' not found - please add it to the system first.";
                continue;
            }

            $staff = User::where('email', $row['email'])->first();
            if (!$staff) {
                $errors[] = "Staff member with email '{$row['email']}' not found - please add them to the system first.";
                continue;
            }

            $deadline = $row['deadline'];

            if ($deadline != '') {
                try {
                    if (!$deadline instanceof \DateTime) {
                        $deadline = Carbon::createFromFormat('d/m/Y H:i', $deadline);


                        if (strpos($row['deadline'], ':') === false) {

                            $deadline->setTime(16, 0, 0);
                        }
                    } else {
                        $deadline = $deadline;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Invalid date format for 'Submission Deadline' for deadline '{$row['deadline']}'.";

                    continue;
                }
            }
            
            $feedbackDeadline = $deadline->copy();
            $feedbackDeadline->addDays(config('assessments.feedback_grace_days'));

            $assessment = Assessment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'type' => $row['assessment_type'],
                    'staff_id' => $staff->id
                ],
                [
                    'feedback_type' => $row['feedback_type'],
                    'deadline' => $deadline,
                    'feedback_deadline' => $feedbackDeadline,
                    'complaints' => 0,
                    'comment' => $row['comment'],
                ]
            );
        }
        return $errors;
    }
}
