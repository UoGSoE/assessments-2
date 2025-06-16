<?php

use App\Models\Assessment;
use App\Livewire\Assessment as AssessmentLivewire;
use App\Livewire\FeedbackReport;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    livewire(AssessmentLivewire::class, ['id' => $assessment->id])
        ->assertSee('Assessment Details')
        ->assertSee('Course')
        ->assertSee('Set By')
        ->assertSee('Assessment Type')
        ->assertSee('Feedback Completed')
        ->assertSee('Complaints Left');
});


it('displays individual assessment details', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    livewire(AssessmentLivewire::class, ['id' => $assessment->id])
        ->assertSee($assessment->course->name)
        ->assertSee($assessment->name)
        ->assertSee($assessment->course->name)
        ->assertSee($assessment->staff->name)
        ->assertSee($assessment->assessment_type)
        ->assertSee($assessment->assessment_date);
});

it('allows feedback completed date to be saved', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    livewire(AssessmentLivewire::class, ['id' => $assessment->id])
        ->assertSee('Select a date')
        ->assertSee('Save Completed Date')
        ->set('feedback_completed_date', '2025-12-12')
        ->call('saveCompletedDate')
        ->assertDontSee('Select a date')
        ->assertDontSee('Save Completed Date');

    expect($assessment->refresh()->feedback_completed_date)->toBe('2025-12-12');

    livewire(FeedbackReport::class)
        ->assertSee('2025-12-12');

});

it('displays all complaints', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);
    $student = User::factory()->create();
    $complaint = Complaint::factory()->create(['assessment_id' => $assessment->id, 'student_id' => $student->id]);

    livewire(AssessmentLivewire::class, ['id' => $assessment->id])
        ->assertSee($complaint->student->name);
    
});

it('deletes assessment', function () {
    $assessment = Assessment::factory()->create();

    livewire(FeedbackReport::class)
        ->assertSee($assessment->type);

    livewire(AssessmentLivewire::class, ['id' => $assessment->id])
        ->call('deleteAssessment');
    
    livewire(FeedbackReport::class)
        ->assertDontSee($assessment->type);
    
});