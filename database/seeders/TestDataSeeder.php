<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::factory()->count(5)->create();
        
        $students = User::factory()->count(10)->create();
        
        $staff = User::factory()->staff()->count(10)->create();

        foreach ($courses as $course) {
            $course->students()->attach(
                $students->random(rand(5, 8))->pluck('id')->toArray()
            );
        }

        foreach ($courses as $course) {
            $course->staff()->attach(
                $staff->random(rand(2, 4))->pluck('id')->toArray()
            );
        }
        
        foreach ($courses as $course) {
            Assessment::factory()->count(rand(0, 3))->create([
                'course_id' => $course->id,
                'staff_id' => fn() => $course->staff->random()->id
            ]);
        }

        $assessments = Assessment::all();
        foreach ($assessments as $assessment) {
            Complaint::factory()->count(rand(0, 3))->create([
                'assessment_id' => $assessment,
                'student_id' => fn() => $assessment->course->students->random()->id,
                'staff_id' => $assessment->staff->id
            ]);
        }
        
        

        
        
        // Assessment::factory()->count(5)->create([
        //     'course_id' => fn() => $courses->random()->id,
        //     'staff_id' => fn() => $staff->random()->id
        // ]);

        //$assessments = Assessment::all();
        //Complaint::factory()->count(5)->create([
        //    'assessment_id' => fn() => $assessments->random()->id,
        //    'student_id' => fn() => $students->random()->id,
        //    'staff_id' => fn() => $staff->random()->id
        //]);
    }
}
