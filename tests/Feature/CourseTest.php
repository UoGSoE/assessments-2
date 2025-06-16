<?php

use App\Models\Assessment;
use App\Livewire\Course as CourseLivewire;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    $course = Course::factory()->create();
    $students = User::factory()->count(3)->create();
    $staff = User::factory()->staff()->create();
    foreach ($students as $student) {
        $student->coursesAsStudent()->attach($course);
    }
    $assessments = Assessment::factory()->count(3)->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    livewire(CourseLivewire::class, ['id' => $course->id])
        ->assertSee('Course Details')
        ->assertSee('Title')
        ->assertSee('Code')
        ->assertSee('Students')
        ->assertSee('Assessments');
});

it('displays individual course details', function () {
    $course = Course::factory()->create();
    $students = User::factory()->count(3)->create();
    $staff = User::factory()->staff()->create();
    foreach ($students as $student) {
        $student->coursesAsStudent()->attach($course);
    }
    $assessments = Assessment::factory()->count(3)->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    livewire(CourseLivewire::class, ['id' => $course->id])
        ->assertSee($course->title)
        ->assertSee($course->code)
        ->assertSee($students->count())
        ->assertSee($assessments->count());
});