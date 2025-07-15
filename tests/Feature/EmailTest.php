<?php

use App\Mail\OverdueFeedback;
use App\Mail\ProblematicAssessment;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

it('contains staff email content', function () {
    $staff = User::factory()->staff()->create();
    $complaints = Complaint::factory()->count(3)->create(['staff_id' => $staff->id]);
    
    $email = new OverdueFeedback($complaints);
    
    $email->assertSeeInHtml('Assessment Feedback');

    foreach ($complaints as $complaint) {
        $email->assertSeeInHtml($complaint->assessment->course->code);
    }
});

it('sends staff email', function () {
    Mail::fake();
    $staff = User::factory()->staff()->create();
    $complaints = Complaint::factory()->count(3)->create(['staff_id' => $staff->id]);
    
    $email = new OverdueFeedback($complaints);
    Mail::to($staff->email)->send($email);

    Mail::assertSent(OverdueFeedback::class);
});

it('contains office email content', function () {
    $assessment = Assessment::factory()->create();
    $email = new ProblematicAssessment($assessment);

    $email->assertSeeInHtml('Problematic Assessment');
    $email->assertSeeInHtml($assessment->course->code);
});

it('correctly identifies problematic assessments', function () {
    $course = Course::factory()->create();
    $student1 = User::factory()->create();
    $student2 = User::factory()->create();
    $student3 = User::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);
    $course->students()->attach([$student1->id, $student2->id, $student3->id]);
    $complaint1 = Complaint::factory()->create(['student_id' => $student1->id, 'assessment_id' => $assessment->id]);
    $complaint2 = Complaint::factory()->create(['student_id' => $student2->id, 'assessment_id' => $assessment->id]);
    $complaint3 = Complaint::factory()->create(['student_id' => $student3->id, 'assessment_id' => $assessment->id]);
    expect($assessment->isProblematic())->toBeTrue();
});

it('sends office email', function () {
    Mail::fake();
    $assessment = Assessment::factory()->create();
    $email = new ProblematicAssessment($assessment);
    Mail::to(config('assessments.office_email'))->send($email);

    Mail::assertSent(ProblematicAssessment::class);
});

// TODO: test that commands work

it('auto signs off assessments', function () {
    $assessment = Assessment::factory()->create(['feedback_deadline' => Carbon::now()->subDays(25)]);
    expect($assessment->canBeAutoSignedOff())->toBeTrue();
    expect($assessment->feedback_completed_date)->toBeNull();
    expect($assessment->complaints->count())->toBe(0);
    
    $this->artisan('assessments:auto-signoff');

    $assessment->refresh();
    expect($assessment->feedback_completed_date)->not->toBeNull();
    
});