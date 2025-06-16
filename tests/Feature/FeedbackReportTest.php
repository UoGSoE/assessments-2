<?php

use App\Livewire\Course;
use App\Livewire\FeedbackReport;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course as ModelsCourse;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can be rendered', function () {
    livewire(FeedbackReport::class)
        ->assertSee('Feedback Report')
        ->assertSee('Search')
        ->assertSee('No assessments found');
});


it('displays assessment details', function () {
    $assessment1 = Assessment::factory()->create();
    $assessment2 = Assessment::factory()->create();
    $assessment3 = Assessment::factory()->create(); 

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
        ->assertSee($assessment1->feedback_deadline)
        ->assertSee($assessment2->feedback_deadline)
        ->assertSee($assessment3->feedback_deadline);

});

it('shows number of complaints for a given assessment', function () {
    $assessment = Assessment::factory()->create();
    $complaint1 = Complaint::factory()->create(['assessment_id' => $assessment->id]);
    $complaint2 = Complaint::factory()->create(['assessment_id' => $assessment->id]);
    $complaint3 = Complaint::factory()->create(['assessment_id' => $assessment->id]);
    
    livewire(FeedbackReport::class)
        ->assertSee(3);
});

// TODO: Establish what exactly should be deleted
it('deletes all data', function () {
    $assessment = Assessment::factory()->create();
    $complaint = Complaint::factory()->count(3)->create(['assessment_id' => $assessment->id]);

    expect(Assessment::count())->toBe(1);
    expect(Complaint::count())->toBe(3);
    
    livewire(FeedbackReport::class)
        ->assertSee($assessment->course->code)
        ->assertSee($assessment->type)
        ->assertSee(3)
        ->call('deleteAllData');

    expect(Assessment::count())->toBe(0);
    expect(Complaint::count())->toBe(0);

    livewire(FeedbackReport::class)
        ->assertSee('No assessments found');
});


it('searches for an assessment', function () {
    $course1 = ModelsCourse::factory()->create(['code' => 'TEST123']);
    $course2 = ModelsCourse::factory()->create(['code' => 'TEST456']);
    $course3 = ModelsCourse::factory()->create(['code' => 'TEST789']);
    $assessment1 = Assessment::factory()->create(['course_id' => $course1->id]);
    $assessment2 = Assessment::factory()->create(['course_id' => $course2->id]);
    $assessment3 = Assessment::factory()->create(['course_id' => $course3->id]);

    livewire(FeedbackReport::class)
        ->assertSee($assessment1->type)
        ->assertSee($assessment2->type)
        ->assertSee($assessment3->type)
        ->set('searchText', '123')
        ->assertSee($assessment1->type)
        ->assertDontSee($assessment2->type)
        ->assertDontSee($assessment3->type);

});
