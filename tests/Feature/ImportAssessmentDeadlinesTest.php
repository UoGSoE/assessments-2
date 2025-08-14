<?php

use App\Importers\Assessments;
use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Livewire\Assessment as LivewireAssessment;
use App\Livewire\FeedbackReport;
use App\Livewire\ImportDeadlinePage;
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
    $this->testAssessmentDeadlines = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['GES1235', 'Moodle Quiz', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', ''],
    ];
    $this->incorrectlyFormattedAssessmentDeadlines = [
        ['Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['GES1235', 'Moodle Quiz', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', 'My exam is great'],
    ];
    $this->missingAssessmentData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['', 'Moodle Quiz', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', '', '26/06/2025 16:07', ''],
    ];
    $this->nonexistentCourseData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['GES1234', 'Moodle Quiz', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', ''],
    ];
    $this->nonexistentStaffData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['GES1235', 'Moodle Quiz', 'Moodle - Graded', 'claire.james@example.com', '26/06/2025 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', ''],
    ];
    $this->incorrectDateFormatData = [
        ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Deadline', 'Comments'],
        ['GES1235', 'Moodle Quiz', 'Moodle - Graded', 'claire.jones@example.com', '2025/06/26 16:07', 'My moodle quiz is great'],
        ['MATH1235', 'Exam', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', ''],
    ];
});

it('can render deadlines import page', function () {
    actingAs($this->admin);

    livewire(ImportDeadlinePage::class)
        ->assertSee('Import Deadlines')
        ->assertSee('course code | assessment type | feedback type | staff email | submission deadline | comments')
        ->assertSee('ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:07 | My moodle quiz is great')
        ->assertSee('Upload');
});

it('allows admin to access import page', function () {
    actingAs($this->admin);

    $this->get('/import/deadlines')
        ->assertOk()
        ->assertSee('Import Deadlines');
});

it('prevents non-admin from accessing import page', function () {
    actingAs($this->user);

    $this->get('/import/deadlines')
        ->assertForbidden();
});

it('requires admin privileges to import deadlines', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('deadlines.xlsx', 100);

    post('/import/deadlines', ['importFile' => $file])
        ->assertForbidden();
});

it('imports assessments with deadlines', function () {
    expect(Assessment::count())->toBe(0);
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    (new Assessments)->process($this->testAssessmentDeadlines);
    expect(Assessment::count())->toBe(2);

    $assessment1 = Assessment::where('course_id', '=', '1')->first();

    expect($assessment1)->not->toBeNull();
    expect($assessment1->course->title)->toBe('Geology Course');
    expect($assessment1->type)->toBe('Moodle Quiz');
    expect($assessment1->feedback_type)->toBe('Moodle - Graded');

    actingAs($this->admin);
    livewire(FeedbackReport::class)->assertSee('Moodle Quiz');

    livewire(LivewireAssessment::class, ['assessment' => $assessment1])
        ->assertSee('Moodle Quiz')
        ->assertSee('Moodle - Graded')
        ->assertSee('2025-06-26');
});

it('does not import deadlines file with wrong format', function () {
    $errors = (new Assessments)->process($this->incorrectlyFormattedAssessmentDeadlines);
    expect(Assessment::count())->toBe(0);
    $this->assertCount(1, $errors);
    $this->assertEquals('Incorrect file format - please check the file and try again.', $errors[0]);
});

it('handles deadlines with missing data', function () {
    $errors = (new Assessments)->process($this->missingAssessmentData);
    expect(Assessment::count())->toBe(0);
    $this->assertCount(2, $errors);
    $this->assertEquals('Row 2: The course code field is required.', $errors[0]);
    $this->assertEquals('Row 3: The email field is required.', $errors[1]);
});

it('handles deadlines with nonexistent course', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new Assessments)->process($this->nonexistentCourseData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Course with code 'GES1234' not found - please add it to the system first.", $errors[0]);
});

it('handles deadlines with nonexistent staff', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new Assessments)->process($this->nonexistentStaffData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Staff member with email 'claire.james@example.com' not found - please add them to the system first.", $errors[0]);
});

it('handles deadlines with incorrect date format', function () {
    (new Courses)->process($this->testCourseData);
    (new StaffCourses)->process($this->testStaffCourseData);
    $errors = (new Assessments)->process($this->incorrectDateFormatData);
    expect(Assessment::count())->toBe(1);
    $this->assertCount(1, $errors);
    $this->assertEquals("Invalid date format for 'Submission Deadline' for deadline '2025/06/26 16:07'.", $errors[0]);
});
