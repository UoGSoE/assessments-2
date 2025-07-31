<?php

use App\Livewire\CreateAssessment;
use App\Livewire\FeedbackReport;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    livewire(CreateAssessment::class)
        ->assertSee('New Assessment')
        ->assertSee('Assessment Type')
        ->assertSee('Staff Feedback Type')
        ->assertSee('Staff')
        ->assertSee('Course')
        ->assertSee('Deadline')
        ->assertSee('Feedback Deadline')
        ->assertSee('Comment')
        ->assertSee('Create');
});

it('is created', function () {
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create(['school' => 'ENG']);
    $course = Course::factory()->create(['school' => 'ENG']);

    actingAs($admin);
    livewire(CreateAssessment::class)
        ->set('assessment_type', 'Quiz 500')
        ->set('staff_feedback_type', 'Moodle')
        ->set('staff_id', $staff->id)
        ->set('course_id', $course->id)
        ->set('deadline', '2025-12-12')
        ->set('feedback_deadline', '2025-12-24')
        ->call('createAssessment');

    livewire(FeedbackReport::class)
        ->assertSee('Quiz 500')
        ->assertSee('Moodle')
        ->assertSee($staff->name)
        ->assertSee($course->code);
});

it('validates the form', function () {
    livewire(CreateAssessment::class)
        ->set('assessment_type', '')
        ->set('staff_feedback_type', '')
        ->set('staff_id', '')
        ->set('course_id', '')
        ->set('deadline', '')
        ->set('feedback_deadline', '')
        ->call('createAssessment')
        ->assertHasErrors(['assessment_type', 'staff_feedback_type', 'staff_id', 'course_id', 'deadline', 'feedback_deadline']);
});

it('only allows admins to create assessments', function () {
    $random_student = User::factory()->create();
    $random_staff = User::factory()->staff()->create();

    actingAs($random_student)->get(route('assessment.create'))
        ->assertForbidden();

    actingAs($random_staff)->get(route('assessment.create'))
        ->assertForbidden();
});

it('creates assessment in database with correct attributes', function () {
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create(['school' => 'ENG']);
    $course = Course::factory()->create(['school' => 'ENG']);

    actingAs($admin);
    livewire(CreateAssessment::class)
        ->set('assessment_type', 'Quiz 500')
        ->set('staff_feedback_type', 'Moodle')
        ->set('staff_id', $staff->id)
        ->set('course_id', $course->id)
        ->set('deadline', '2025-12-12')
        ->set('feedback_deadline', '2025-12-24')
        ->call('createAssessment');

    $this->assertDatabaseHas('assessments', [
        'type' => 'Quiz 500',
        'feedback_type' => 'Moodle',
        'staff_id' => $staff->id,
        'course_id' => $course->id,
        'deadline' => '2025-12-12 00:00:00',
        'feedback_deadline' => '2025-12-24 00:00:00',
    ]);
});

it('validates date format for deadlines', function () {
    livewire(CreateAssessment::class)
        ->set('assessment_type', 'Quiz')
        ->set('staff_feedback_type', 'Moodle')
        ->set('staff_id', 1)
        ->set('course_id', 1)
        ->set('deadline', 'invalid-date')
        ->set('feedback_deadline', '2025/12/25')
        ->call('createAssessment')
        ->assertHasErrors(['deadline']);
});
