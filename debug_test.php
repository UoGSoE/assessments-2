<?php

use App\Livewire\Assessment as AssessmentLivewire;
use App\Livewire\FeedbackReport;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

// Replicate the test setup
$admin = User::factory()->create(['is_admin' => true]);
$course = Course::factory()->create();
$staff = User::factory()->staff()->create();
$course->staff()->attach($staff->id);
$assessment = Assessment::factory()->create([
    'course_id' => $course->id, 
    'staff_id' => $staff->id, 
    'feedback_deadline' => now()->subDays(1), 
    'deadline' => now()->subDays(1)
]);

actingAs($staff);

// Save the completed date
livewire(AssessmentLivewire::class, ['assessment' => $assessment])
    ->set('feedback_completed_date', '2025-12-12')
    ->call('saveCompletedDate');

// Check what's in the database
$updated = $assessment->fresh();
echo "Database feedback_completed_date: " . ($updated->feedback_completed_date ? $updated->feedback_completed_date->format('Y-m-d') : 'NULL') . "\n";
echo "Database feedback_completed_date formatted: " . ($updated->feedback_completed_date ? $updated->feedback_completed_date->format('d/m/Y') : 'NULL') . "\n";

// Check what FeedbackReport shows
$feedbackReportComponent = livewire(FeedbackReport::class);
echo "FeedbackReport HTML contains '12/12/2025': " . (str_contains($feedbackReportComponent->html(), '12/12/2025') ? 'YES' : 'NO') . "\n";

// Let's see what assessments the FeedbackReport component has
$assessments = $feedbackReportComponent->get('assessments');
echo "Number of assessments in FeedbackReport: " . count($assessments) . "\n";

foreach ($assessments as $ass) {
    if ($ass->id === $assessment->id) {
        echo "Found our assessment in FeedbackReport\n";
        echo "Feedback completed date: " . ($ass->feedback_completed_date ? $ass->feedback_completed_date->format('d/m/Y') : 'NULL') . "\n";
        break;
    }
}
