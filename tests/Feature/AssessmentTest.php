<?php

use App\Models\Assessment;
use App\Livewire\Assessment as AssessmentLivewire;
use App\Livewire\FeedbackReport;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id, 'feedback_deadline' => now()->subDays(1)]);

    actingAs($this->admin);

    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertSee('Assessment Details')
        ->assertSee('Course')
        ->assertSee('Set By')
        ->assertSee('Assessment Type')
        ->assertSee('Feedback Completed')
        ->assertSee('Complaints Left')
        ->assertDontSee('Add Complaint');
});


it('displays individual assessment details', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    actingAs($this->admin);

    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertSee($assessment->course->title)
        ->assertSee($assessment->type)
        ->assertSee($assessment->course->code)
        ->assertSee($assessment->staff->name)
        ->assertSee($assessment->type)
        ->assertSee($assessment->deadline);
});

it('allows feedback completed date to be saved', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    actingAs($staff);
    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
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
    $complaint = Complaint::factory()->create(['assessment_id' => $assessment->id, 'student_id' => $student->id, 'staff_id' => $staff->id]);

    actingAs($this->admin);
    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertSee($complaint->student->name);
    
});

it('deletes assessment', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id]);

    actingAs($this->admin);
    livewire(FeedbackReport::class)
        ->assertSee($assessment->type);

    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->call('deleteAssessment');
    
    livewire(FeedbackReport::class)
        ->assertDontSee($assessment->type);
    
});

it('allows students to add complaints', function () {
    $course = Course::factory()->create();
    $staff = User::factory()->staff()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'staff_id' => $staff->id, 'feedback_deadline' => now()->subDays(1)]);
    $student = User::factory()->create();

    actingAs($student);
    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertDontSee('Add Complaint');

    $course->students()->attach($student->id);

    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertSee('Add Complaint')
        ->call('addComplaint')
        ->assertSee($student->name);

    expect($assessment->refresh()->complaints->count())->toBe(1);

    livewire(AssessmentLivewire::class, ['assessment' => $assessment])
        ->assertSee('Complaints Left')
        ->assertSee('1');
});