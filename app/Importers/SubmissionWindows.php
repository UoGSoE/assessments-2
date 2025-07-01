<?php

namespace App\Importers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;

class SubmissionWindows
{
    /**
     * Create a new class instance.
     */
    public function process($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $row = [
                'course_code' => $row[0],
                'assessment_type' => $row[1],
                'feedback_type' => $row[2],
                'email' => $row[3],
                'submission_window_start' => $row[4],
                'submission_window_end' => $row[5],
                'comment' => $row[6],
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

            $start = $row['submission_window_start'];
            $end = $row['submission_window_end'];

            if ($start != '') {
                try {
                    if (!$start instanceof \DateTime) {
                        $start = Carbon::createFromFormat('d/m/Y', $start);


                        if (strpos($row['submission_window_start'], ':') === false) {

                            $start->setTime(16, 0, 0);
                        }
                    } else {
                        $start = $start;
                    }
                } catch (\Exception $e) {
                    dd($start);
                    $errors[] = "Invalid date format for 'Submission Deadline' for deadline '{$row['submission_window_start']}'.";

                    continue;
                }
            }

            if ($end != '') {
                try {
                    if (!$end instanceof \DateTime) {
                        $end = Carbon::createFromFormat('d/m/Y', $end);


                        if (strpos($row['submission_window_end'], ':') === false) {

                            $end->setTime(16, 0, 0);
                        }
                    } else {
                        $end = $end;
                    }
                } catch (\Exception $e) {
                    dd($end);
                    $errors[] = "Invalid date format for 'Submission Deadline' for deadline '{$row['submission_window_end']}'.";

                    continue;
                }
            }


            $feedbackDeadline = $end->addDays(21);

            $assessment = Assessment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'type' => $row['assessment_type'],
                    'staff_id' => $staff->id
                ],
                [
                    'feedback_type' => $row['feedback_type'],
                    'submission_window_start' => $start,
                    'submission_window_end' => $end,
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
