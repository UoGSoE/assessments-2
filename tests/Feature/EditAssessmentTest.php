<?php

use App\Livewire\EditAssessment;
use App\Livewire\FeedbackReport;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'school' => 'ENG']);
    $this->course = Course::factory()->create(['school' => 'ENG']);
    $this->staff = User::factory()->staff()->create();
    $this->assessment = Assessment::factory()->create([
        'deadline' => '2025-12-12',
        'type' => 'Quiz 500',
        'course_id' => $this->course->id,
        'staff_id' => $this->staff->id,
        'feedback_type' => 'Moodle',
        'feedback_deadline' => '2025-12-24',
    ]);
});

it('can be rendered', function () {
    actingAs($this->admin);
    livewire(EditAssessment::class, ['id' => $this->assessment->id])
        ->assertSee('Edit Assessment')
        ->assertSee('Assessment Type')
        ->assertSee('Staff Feedback Type')
        ->assertSee('Staff')
        ->assertSee('Course')
        ->assertSee('Deadline')
        ->assertSee('Feedback Deadline')
        ->assertSee('Comment')
        ->assertSee('Update');
});

it('is updated', function () {
    $new_staff = User::factory()->staff()->create();
    $new_course = Course::factory()->create(['school' => 'ENG']);

    actingAs($this->admin);
    livewire(EditAssessment::class, ['id' => $this->assessment->id])
        ->set('assessment_type', 'Quiz 501')
        ->set('staff_feedback_type', 'Canvas')
        ->set('staff_id', $new_staff->id)
        ->set('course_id', $new_course->id)
        ->set('deadline', '2025-12-13')
        ->set('feedback_deadline', '2025-12-25')
        ->call('updateAssessment');

    livewire(FeedbackReport::class)
        ->assertSee('Quiz 501')
        ->assertSee('Canvas')
        ->assertSee($new_staff->name)
        ->assertSee($new_course->code);

    $this->assertDatabaseHas('assessments', [
        'id' => $this->assessment->id,
        'type' => 'Quiz 501',
        'feedback_type' => 'Canvas',
        'staff_id' => $new_staff->id,
        'course_id' => $new_course->id,
        'deadline' => '2025-12-13 00:00:00',
        'feedback_deadline' => '2025-12-25 00:00:00',
    ]);
});

it('validates the form', function () {
    livewire(EditAssessment::class, ['id' => $this->assessment->id])
        ->set('assessment_type', '')
        ->set('staff_feedback_type', '')
        ->set('staff_id', '')
        ->set('course_id', '')
        ->set('deadline', '')
        ->set('feedback_deadline', '')
        ->call('updateAssessment')
        ->assertHasErrors(['assessment_type', 'staff_feedback_type', 'staff_id', 'course_id', 'deadline', 'feedback_deadline']);
});

it('only allows admins to edit assessments', function () {
    $random_student = User::factory()->create();
    $random_staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create();

    actingAs($random_student)->get(route('assessment.edit', $assessment->id))
        ->assertForbidden();

    actingAs($random_staff)->get(route('assessment.edit', $assessment->id))
        ->assertForbidden();
});
