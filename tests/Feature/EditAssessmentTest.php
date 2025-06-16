<?php

use App\Models\Assessment;
use App\Livewire\CreateAssessment;
use App\Livewire\EditAssessment;
use App\Livewire\FeedbackReport;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create([
        'deadline' => '2025-12-12',
        'type' => 'Quiz 500', 
        'course_id' => $course->id, 
        'staff_id' => $staff->id,
        'feedback_type' => 'Moodle',
        'feedback_deadline' => '2025-12-24',
    ]);

    livewire(EditAssessment::class, ['id' => $assessment->id])
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
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create([
        'deadline' => '2025-12-12',
        'type' => 'Quiz 500', 
        'course_id' => $course->id, 
        'staff_id' => $staff->id,
        'feedback_type' => 'Moodle',
        'feedback_deadline' => '2025-12-24',
    ]);

    $new_staff = User::factory()->staff()->create();
    $new_course = Course::factory()->create();

    livewire(EditAssessment::class, ['id' => $assessment->id])
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
});
