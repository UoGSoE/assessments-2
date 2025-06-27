<?php

namespace App\Importers;

class SubmissionWindows
{
    /**
     * Create a new class instance.
     */
    public function process($rows)
    {
        $errors = [];
        foreach ($rows as $row) {
            $row = [
                'course_code' => $row[0],
                'assessment_type' => $row[1],
                'feedback_type' => $row[2],
                'email' => $row[3],
                'submission_window_start' => $row[2],
                'submission_window_end' => $row[3],
                'comments' => $row[4],
            ];
        }
    }
}
