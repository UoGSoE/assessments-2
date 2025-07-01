<?php

use App\Livewire\Course;
use App\Livewire\FeedbackReport;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course as ModelsCourse;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    livewire(FeedbackReport::class)
        ->assertSee('Feedback Report')
        ->assertSee('Search')
        ->assertSee('No assessments found');
});


it('displays assessment details', function () {
    $assessment1 = Assessment::factory()->create(['staff_id' => User::factory()->staff()->create()->id, 'course_id' => ModelsCourse::factory()->create()->id]);
    $assessment2 = Assessment::factory()->create(['staff_id' => User::factory()->staff()->create()->id, 'course_id' => ModelsCourse::factory()->create()->id]);
    $assessment3 = Assessment::factory()->create(['staff_id' => User::factory()->staff()->create()->id, 'course_id' => ModelsCourse::factory()->create()->id]);

    actingAs($this->admin);
    livewire(FeedbackReport::class)
        ->assertSee($assessment1->course->code)
        ->assertSee($assessment2->course->code)
        ->assertSee($assessment3->course->code)
        ->assertSee($assessment1->level)
        ->assertSee($assessment2->level)
        ->assertSee($assessment3->level)
        ->assertSee($assessment1->type)
        ->assertSee($assessment2->type)
        ->assertSee($assessment3->type)
        ->assertSee($assessment1->feedback_type)
        ->assertSee($assessment2->feedback_type)
        ->assertSee($assessment3->feedback_type)
        ->assertSee($assessment1->staff->name)
        ->assertSee($assessment2->staff->name)
        ->assertSee($assessment3->staff->name)
        ->assertSee($assessment1->feedback_deadline->format('d/m/Y'))
        ->assertSee($assessment2->feedback_deadline->format('d/m/Y'))
        ->assertSee($assessment3->feedback_deadline->format('d/m/Y'));

});

describe('showing all forms of data and deleting them', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->course = ModelsCourse::factory()->create();
        $this->staff = User::factory()->staff()->create();
        $this->student = User::factory()->create();
        $this->course->staff()->attach($this->staff);
        $this->course->students()->attach($this->student);
        $this->assessment = Assessment::factory()->create(['staff_id' => $this->staff->id, 'course_id' => $this->course->id]);
    });

    it('shows number of complaints for a given assessment', function () {
        $complaint1 = Complaint::factory()->create(['assessment_id' => $this->assessment->id, 'staff_id' => $this->staff->id, 'student_id' => $this->student->id]);
        $complaint2 = Complaint::factory()->create(['assessment_id' => $this->assessment->id, 'staff_id' => $this->staff->id, 'student_id' => $this->student->id]);
        $complaint3 = Complaint::factory()->create(['assessment_id' => $this->assessment->id, 'staff_id' => $this->staff->id, 'student_id' => $this->student->id]);

        actingAs($this->admin);
        livewire(FeedbackReport::class)
            ->assertSee(3);
    });

    // TODO: Establish what exactly should be deleted
    it('deletes all data', function () {
        $complaint = Complaint::factory()->count(3)->create(['assessment_id' => $this->assessment->id, 'staff_id' => $this->staff->id, 'student_id' => $this->student->id]);

        expect(Assessment::count())->toBe(1);
        expect(Complaint::count())->toBe(3);

        actingAs($this->admin);
        livewire(FeedbackReport::class)
            ->assertSee($this->assessment->course->code)
            ->assertSee($this->assessment->type)
            ->assertSee(3)
            ->call('deleteAllData');

        expect(Assessment::count())->toBe(0);
        expect(Complaint::count())->toBe(0);

        livewire(FeedbackReport::class)
            ->assertSee('No assessments found');
    });
});

it('searches for an assessment', function () {
    $course1 = ModelsCourse::factory()->create(['code' => 'TEST123']);
    $course2 = ModelsCourse::factory()->create(['code' => 'TEST456']);
    $course3 = ModelsCourse::factory()->create(['code' => 'TEST789']);
    $assessment1 = Assessment::factory()->create(['course_id' => $course1->id, 'type' => 'Exam']);
    $assessment2 = Assessment::factory()->create(['course_id' => $course2->id, 'type' => 'Quiz']);
    $assessment3 = Assessment::factory()->create(['course_id' => $course3->id, 'type' => 'Essay']);

    livewire(FeedbackReport::class)
        ->assertSee($assessment1->type)
        ->assertSee($assessment2->type)
        ->assertSee($assessment3->type)
        ->set('searchText', '123')
        ->assertSee($assessment1->type)
        ->assertDontSee($assessment2->type)
        ->assertDontSee($assessment3->type);

});

it('only allows admins to view page', function () {
    $random_student = User::factory()->create();
    $random_staff = User::factory()->staff()->create();

    actingAs($random_student)->get(route('assessment.index'))
        ->assertForbidden();

    actingAs($random_staff)->get(route('assessment.index'))
        ->assertForbidden();
});