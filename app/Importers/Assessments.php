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
        foreach ($rows as $row) {
            $row = [
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
                    dd($deadline);
                    $errors[] = "Invalid date format for 'Submission Deadline' for deadline '{$row['deadline']}'.";

                    continue;
                }
            }


            $feedbackDeadline = $deadline->addDays(21);

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
                    // TODO: import complaints
                    'complaints' => 0,
                    'comment' => $row['comment'],
                ]
            );
        }
        return $errors;
    }
}
