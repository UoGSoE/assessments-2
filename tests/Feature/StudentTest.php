<?php

use App\Livewire\FeedbackReport;
use App\Livewire\Staff;
use App\Livewire\StaffList;
use App\Livewire\Student;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    $student = User::factory()->create();

    livewire(Student::class, ['id' => $student->id])
        ->assertSee('Student Details')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Courses')
        ->assertSee('No courses found')
        ->assertSee('Assessments')
        ->assertSee('No assessments found');
});

it('displays student details', function () {
    $student = User::factory()->create();
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();
    $student->coursesAsStudent()->attach($course1);
    $student->coursesAsStudent()->attach($course2);
    $assessment1 = Assessment::factory()->create(['course_id' => $course1->id]);

    livewire(Student::class, ['id' => $student->id])
        ->assertSee($student->name)
        ->assertSee($student->email)
        ->assertSee($course1->code)
        ->assertSee($course2->code)
        ->assertSee($assessment1->type);
}); // Fixed missing closing brace