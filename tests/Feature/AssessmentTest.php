<?php

use App\Livewire\Assessment as AssessmentLivewire;
use App\Livewire\FeedbackReport;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->course = Course::factory()->create();
    $this->staff = User::factory()->staff()->create();
    $this->course->staff()->attach($this->staff->id);
    $this->assessment = Assessment::factory()->create(['course_id' => $this->course->id, 'staff_id' => $this->staff->id, 'feedback_deadline' => now()->subDays(1), 'deadline' => now()->subDays(1)]);
});

it('can be rendered', function () {

    actingAs($this->admin);

    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee('Assessment Details')
        ->assertSee('Course')
        ->assertSee('Set By')
        ->assertSee('Assessment Type')
        ->assertSee('Feedback Completed')
        ->assertSee('Feedbacks Left')
        ->assertDontSee('Report assessment feedback is overdue');
});

it('displays individual assessment details', function () {

    actingAs($this->admin);

    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee($this->assessment->course->title)
        ->assertSee($this->assessment->type)
        ->assertSee($this->assessment->course->code)
        ->assertSee($this->assessment->staff->name)
        ->assertSee($this->assessment->type)
        ->assertSee($this->assessment->deadline);
});

it('allows feedback completed date to be saved', function () {

    actingAs($this->staff);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee('Select a date')
        ->assertSee('Save Completed Date')
        ->set('feedback_completed_date', '2025-12-12')
        ->call('saveCompletedDate')
        ->assertDontSee('Select a date')
        ->assertDontSee('Save Completed Date');

    expect($this->assessment->refresh()->feedback_completed_date->format('Y-m-d'))->toBe('2025-12-12');

    $this->assertDatabaseHas('assessments', [
        'id' => $this->assessment->id,
        'feedback_completed_date' => '2025-12-12 00:00:00',
    ]);

    livewire(FeedbackReport::class)
        ->assertSee('12/12/2025');

});

it('displays all complaints', function () {
    $student = User::factory()->create();
    $complaint = Complaint::factory()->create(['assessment_id' => $this->assessment->id, 'student_id' => $student->id, 'staff_id' => $this->staff->id]);

    actingAs($this->admin);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee($complaint->student->name);

});

it('deletes assessment', function () {

    actingAs($this->admin);
    livewire(FeedbackReport::class)
        ->assertSee($this->assessment->type);

    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->call('deleteAssessment');

    livewire(FeedbackReport::class)
        ->assertDontSee($this->assessment->type);

    $this->assertDatabaseMissing('assessments', [
        'id' => $this->assessment->id,
    ]);
});

it('allows students to add complaints', function () {
    $student = User::factory()->create();

    actingAs($student);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertDontSee('Report assessment feedback is overdue');

    $this->course->students()->attach($student->id);

    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee('Report assessment feedback is overdue')
        ->call('addComplaint');

    expect($this->assessment->refresh()->complaints->count())->toBe(1);

    $this->assertDatabaseHas('complaints', [
        'assessment_id' => $this->assessment->id,
        'student_id' => $student->id,
        'staff_id' => $this->staff->id,
        'staff_notified' => false,
    ]);

    actingAs($this->staff);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee($student->name);
});

it('does not allow students to complain twice', function () {
    $student = User::factory()->create();
    $this->course->students()->attach($student->id);
    actingAs($student);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee('Report assessment feedback is overdue')
        ->call('addComplaint')
        ->assertDontSee('Report assessment feedback is overdue');
});

it('does not allow complaints on old assessments', function () {
    $oldAssessment = Assessment::factory()->create(['course_id' => $this->course->id, 'staff_id' => $this->staff->id, 'deadline' => now()->subDays(100)]);
    $student = User::factory()->create();
    $this->course->students()->attach($student->id);
    actingAs($student);
    livewire(AssessmentLivewire::class, ['assessment' => $oldAssessment])
        ->assertDontSee('Report assessment feedback is overdue');
});

it('only allows admins to edit or delete assessments', function () {

    actingAs($this->staff);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertDontSee('delete-assessment')
        ->assertDontSee('edit-assessment');
    actingAs($this->admin);
    livewire(AssessmentLivewire::class, ['assessment' => $this->assessment])
        ->assertSee('delete-assessment')
        ->assertSee('edit-assessment');
});

it('cannot be viewed by students and staff from other courses', function () {
    $otherCourseStudent = User::factory()->create();
    $otherCourseStaff = User::factory()->staff()->create();

    actingAs($otherCourseStudent)->get(route('assessment.show', $this->assessment->id))
        ->assertForbidden();

    actingAs($otherCourseStaff)->get(route('assessment.show', $this->assessment->id))
        ->assertForbidden();

});
