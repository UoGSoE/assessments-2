<?php

use App\Livewire\Student;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    $student = User::factory()->create();

    actingAs($this->admin);
    livewire(Student::class, ['id' => $student->id])
        ->assertSee('Student Details')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Courses')
        ->assertSee('Assessments');
});

it('displays student details', function () {
    $student = User::factory()->create();
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();
    $student->coursesAsStudent()->attach($course1);
    $student->coursesAsStudent()->attach($course2);
    $assessment1 = Assessment::factory()->create(['course_id' => $course1->id]);

    actingAs($this->admin);
    livewire(Student::class, ['student' => $student])
        ->assertSee($student->name)
        ->assertSee($student->email)
        ->assertSee($course1->code)
        ->assertSee($course2->code);
});

it('only allows admins and staff to view page', function () {
    $random_student = User::factory()->create();
    $random_staff = User::factory()->staff()->create();

    actingAs($random_student)->get(route('student.show', $random_student->id))
        ->assertForbidden();

    actingAs($random_staff)->get(route('student.show', $random_student->id))
        ->assertSee('Student Details');
});
