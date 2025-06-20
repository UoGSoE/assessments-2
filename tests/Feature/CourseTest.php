<?php

use App\Models\Assessment;
use App\Livewire\Course as CourseLivewire;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->course = Course::factory()->create();
    $this->students = User::factory()->count(3)->create();
    $this->staff = User::factory()->staff()->create();
    foreach ($this->students as $student) {
        $student->coursesAsStudent()->attach($this->course);
    }
});

it('can be rendered', function () {

    actingAs($this->admin);
    livewire(CourseLivewire::class, ['course' => $this->course])
        ->assertSee('Course Details')
        ->assertSee('Title')
        ->assertSee('Code')
        ->assertSee('Students')
        ->assertSee('Assessments');
});

it('displays individual course details', function () {
    $assessments = Assessment::factory()->count(3)->create(['course_id' => $this->course->id, 'staff_id' => $this->staff->id]);

    actingAs($this->admin);
    livewire(CourseLivewire::class, ['course' => $this->course])
        ->assertSee($this->course->title)
        ->assertSee($this->course->code)
        ->assertSee($this->students->count())
        ->assertSee($assessments->count());

    $student = User::factory()->create();

    actingAs($student);
    livewire(CourseLivewire::class, ['course' => $this->course])
        ->assertDontSee('Students');
});

it('cannot be viewed by students and staff from other courses', function () {
    $otherCourseStudent = User::factory()->create();
    $otherCourseStaff = User::factory()->staff()->create();

    actingAs($otherCourseStudent)->get(route('course.show', $this->course->id))
        ->assertForbidden();

    actingAs($otherCourseStaff)->get(route('course.show', $this->course->id))
        ->assertForbidden();

});