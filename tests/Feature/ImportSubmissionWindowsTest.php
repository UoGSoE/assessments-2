<?php

use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Importers\SubmissionWindows;
use App\Livewire\Assessment as LivewireAssessment;
use App\Livewire\FeedbackReport;
use App\Livewire\ImportSubmissionWindowPage;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'is_staff' => false]);
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->testCourseData = [
        ['Course Title', 'Code', 'Discipline', 'Active (Yes/No)'],
        ['Geology Course', 'GES1235', 'Ignore', 'Yes'],
        ['Maths Course', 'MATH1235', 'Ignore', 'Yes'],
        ['Chemistry Course', 'CHEM1112', 'Ignore', 'Yes'],
        ['Engineering Course', 'ENG1213', 'Ignore', 'Yes'],
    ];
    $this->testStaffCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Email', 'Course Code'],
        ['Claire', 'Jones', 'cls2x', 'claire.jones@example.com', 'GES1235'],
        ['Tad', 'Murray', 'tad2x', 'tad.murray@example.com', 'MATH1235'],
    ];
    $this->testAssessmentSubmissionWindows = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['GES1235', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
    ];
    $this->incorrectlyFormattedAssessmentSubmissionWindows = [
        ['Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['GES1235', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
    ];
    $this->missingAssessmentSubmissionWindowData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '', ''],
    ];
    $this->nonexistentCourseData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['GES1234', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
    ];
    $this->nonexistentStaffData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['GES1235', 'Group Assignment', 'Moodle - Graded', 'claire.james@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
    ];
    $this->incorrectDateFormatData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
        ['GES1235', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '2025/06/26 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
    ];
});

it('can render submission-windows import page', function () {
    actingAs($this->admin);

    livewire(ImportSubmissionWindowPage::class)
        ->assertSee('Import Submission Windows')
        ->assertSee('course code | assessment type | feedback type | staff email | submission window from | submission window to | comments')
        ->assertSee('ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:08 | 27/06/2025 16:08 | My moodle quiz is great')
        ->assertSee('Upload');
});

it('allows admin to access import page', function () {
    actingAs($this->admin);

    $this->get('/import/submission-windows')
        ->assertOk()
        ->assertSee('Import Submission Windows');
});

it('prevents non-admin from accessing import page', function () {
    actingAs($this->user);

    $this->get('/import/submission-windows')
        ->assertForbidden();
});

it('requires admin privileges to import submission-windows', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('submission-windows.xlsx', 100);

    post('/import/submission-windows', ['importFile' => $file])
        ->assertForbidden();
});

it('imports assessments with submission windows', function () {
    expect(Assessment::count())->toBe(0);
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new SubmissionWindows)->process($this->testAssessmentSubmissionWindows);
    expect(Assessment::count())->toBe(2);

    $assessment1 = Assessment::where('course_id', '=', '1')->first();

    expect($assessment1)->not->toBeNull();
    expect($assessment1->course->title)->toBe('Geology Course');
    expect($assessment1->type)->toBe('Group Assignment');
    expect($assessment1->feedback_type)->toBe('Moodle - Graded');

    actingAs($this->admin);
    livewire(FeedbackReport::class)->assertSee('Group Assignment');

    livewire(LivewireAssessment::class, ['assessment' => $assessment1])
        ->assertSee('Group Assignment')
        ->assertSee('Moodle - Graded');
});

it('does not import submission windows file with wrong format', function () {
    $errors = (new SubmissionWindows)->process($this->incorrectlyFormattedAssessmentSubmissionWindows);
    expect(Assessment::count())->toBe(0);
    $this->assertCount(1, $errors);
    $this->assertEquals('Incorrect file format - please check the file and try again.', $errors[0]);
});

it('handles submission windows with missing data', function () {
    $errors = (new SubmissionWindows)->process($this->missingAssessmentSubmissionWindowData);
    expect(Assessment::count())->toBe(0);
    $this->assertCount(2, $errors);
    $this->assertEquals('Row 2: The course code field is required.', $errors[0]);
    $this->assertEquals('Row 3: The submission window end field is required.', $errors[1]);
});

it('handles submission windows with nonexistent course', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new SubmissionWindows)->process($this->nonexistentCourseData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Course with code 'GES1234' not found - please add it to the system first.", $errors[0]);
});

it('handles submission windows with nonexistent staff', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new SubmissionWindows)->process($this->nonexistentStaffData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Staff member with email 'claire.james@example.com' not found - please add them to the system first.", $errors[0]);
});

it('handles submission windows with incorrect date format', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new SubmissionWindows)->process($this->incorrectDateFormatData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Invalid date format for 'Submission Deadline' for deadline '2025/06/26 16:07'.", $errors[0]);
});
