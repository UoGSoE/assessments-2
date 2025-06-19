<?php

use App\Livewire\FeedbackReport;
use App\Livewire\Staff;
use App\Livewire\StaffList;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    $staff = User::factory()->staff()->create();
    actingAs($this->admin);
    livewire(Staff::class, ['id' => $staff->id])
        ->assertSee('Staff Details')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Courses')
        ->assertSee('No courses found')
        ->assertSee('Assessments')
        ->assertSee('No assessments found');
});

it('displays staff details', function () {
    $staff = User::factory()->staff()->create();
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();
    $staff->coursesAsStaff()->attach($course1);
    $staff->coursesAsStaff()->attach($course2);
    $assessment = Assessment::factory()->create(['staff_id' => $staff->id, 'course_id' => $course1->id]);

    actingAs($this->admin);
    livewire(Staff::class, ['staff' => $staff])
        ->assertSee($staff->name)
        ->assertSee($staff->email)
        ->assertSee($course1->code)
        ->assertSee($course2->code)
        ->assertSee($assessment->type);
});

