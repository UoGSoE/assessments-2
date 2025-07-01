<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ImportJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:import-json {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import JSON dump from old system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        $this->info('Importing staff users...' . count($data['users']['staff']));

        $passwords  = [];
        foreach(range(1, 5) as $i) {
            $passwords[] = \Illuminate\Support\Facades\Hash::make(Str::random(32), ['rounds' => 12]);
        }

        $oldUserMap = [];

        foreach ($data['users']['staff'] as $user) {
            $this->info('Importing user: ' . $user['email']);
            $user = User::firstOrCreate([
                'email' => $user['email'],
            ], [
                'username' => $user['username'],
                'email' => $user['email'],
                'surname' => $user['surname'],
                'forenames' => $user['forenames'],
                'is_admin' => $user['is_admin'],
                'is_staff' => true,
                'school' => $user['is_admin'] ? 'ENG' : null,
                'password' => Arr::random($passwords)
            ]);
            $oldUserMap[$user['id']] = $user->id;
        }



        $this->info('Importing student users...' . count($data['users']['students']));

        foreach ($data['users']['students'] as $user) {
            $user = User::firstOrCreate([
                'email' => $user['email'],
            ], [
                'username' => $user['username'],
                'email' => $user['email'],
                'surname' => $user['surname'],
                'forenames' => $user['forenames'],
                'is_admin' => false,
                'is_staff' => false,
                'school' => null,
                'password' => Arr::random($passwords)
            ]);
            $oldUserMap[$user['id']] = $user->id;
        }

        $oldCourseMap = [];

        $this->info('Importing courses...' . count($data['courses']));

        foreach ($data['courses'] as $course) {    
            $course = Course::firstOrCreate([
               'code' => $course['code'],
            ], [
                'title' => $course['title'],
                'discipline' => $course['discipline'],
                'year' => $course['year'],
                'is_active' => $course['is_active'],
                'school' => 'ENG'
            ]);
            $oldCourseMap[$course['id']] = $course->id;
        }

        $oldAssessmentMap = [];

        $this->info('Importing assessments...' . count($data['assessments']));

        foreach ($data['assessments'] as $assessment) {
            $newCourseId = null;
            if (!isset($oldCourseMap[$assessment['course_id']])) {
                $course = Course::where('code', $assessment['course']['code'])->first();
                if (!$course) {
                    $this->info('Course not found: ' . $assessment['course']['code']);
                    continue;
                }
                $oldCourseMap[$assessment['course_id']] = $course->id;
            }

            $assessment = Assessment::firstOrCreate([
                'type' => $assessment['type'],
                'feedback_type' => $assessment['feedback_type'],
                'staff_id' => $oldUserMap[$assessment['staff_id']],
                'course_id' => $oldCourseMap[$assessment['course_id']],
            ], [
                'deadline' => $assessment['deadline']['date'],
                'feedback_deadline' => $assessment['feedback_due']['date'],
                'feedback_completed_date' => $assessment['feedback_left']['date'] ?? null,
                'submission_window_from' => null,
                'submission_window_to' => null,
                'comments' => $assessment['comment'],
            ]);
            $oldAssessmentMap[$assessment['id']] = $assessment->id;
        }

        $this->info('Importing course student allocations...' . count($data['relationships']['course_students']));

        foreach ($data['relationships']['course_students'] as $courseStudent) {
            $course = Course::find($oldCourseMap[$courseStudent['course_id']]);
            if (!$course) {
                $this->info('Course not found: ' . $courseStudent['course_id']);
                continue;
            }
            $course->students()->attach($oldUserMap[$courseStudent['student_id']]);
        }

        $this->info('Importing course staff allocations...' . count($data['relationships']['course_staff']));

        foreach ($data['relationships']['course_staff'] as $courseStaff) {
            if (!isset($oldUserMap[$courseStaff['staff_id']])) {
                $this->info('Staff user not found: ' . $courseStaff['staff_id']);
                continue;
            }
            if (!isset($oldCourseMap[$courseStaff['course_id']])) {
                $this->info('Course not found: ' . $courseStaff['course_id']);
                continue;
            }
            $course = Course::findOrFail($oldCourseMap[$courseStaff['course_id']]);
            $course->staff()->attach($oldUserMap[$courseStaff['staff_id']]);
        }
    }
    
}