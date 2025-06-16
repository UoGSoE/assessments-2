<?php

use App\Models\Assessment;
use App\Livewire\CreateAssessment;
use App\Livewire\FeedbackReport;
use App\Models\Complaint;
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
    $course = Course::factory()->create();

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

