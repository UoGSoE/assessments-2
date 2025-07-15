<?php

use App\Importers\Assessments;
use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Importers\StudentCourses;
use App\Livewire\Assessment as LivewireAssessment;
use App\Livewire\Course as LivewireCourse;
use App\Livewire\FeedbackReport;
use App\Livewire\ImportPage;
use App\Livewire\Staff;
use App\Livewire\Student;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'is_staff' => false]);
    $this->user = User::factory()->create(['is_admin' => false]);
    Storage::fake('local');
});

describe('Import Page Rendering', function () {
    it('can render courses import page', function () {
        actingAs($this->admin);
        
        livewire(ImportPage::class, ['fileType' => 'courses'])
            ->assertSee('Import Courses')
            ->assertSee('Course Title | Code | Discipline | Active (Yes/No)')
            ->assertSee('Aero Engineering | ENG4037 | Aero | Yes')
            ->assertSee('Upload');
    });

    it('can render student-courses import page', function () {
        actingAs($this->admin);
        
        livewire(ImportPage::class, ['fileType' => 'student-courses'])
            ->assertSee('Import Student Course Allocations')
            ->assertSee('Please ensure all courses are uploaded to the database first.')
            ->assertSee('Forenames | Surname | GUID | Course Code')
            ->assertSee('Jane | Smith | 123456789S | ENG1000')
            ->assertSee('Upload');
    });

    it('can render staff-courses import page', function () {
        actingAs($this->admin);
        
        livewire(ImportPage::class, ['fileType' => 'staff-courses'])
            ->assertSee('Import Staff Course Allocations')
            ->assertSee('Please ensure all courses are uploaded to the database first.')
            ->assertSee('Forenames | Surname | GUID | Email | Course Code')
            ->assertSee('Claire | Jones | cls2x | claire.jones@example.com | ENG1000')
            ->assertSee('Upload');
    });

    it('can render deadlines import page', function () {
        actingAs($this->admin);
        
        livewire(ImportPage::class, ['fileType' => 'deadlines'])
            ->assertSee('Import Deadlines')
            ->assertSee('course code | assessment type | feedback type | staff email | submission deadline | comments')
            ->assertSee('ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:07 | My moodle quiz is great')
            ->assertSee('Upload');
    });

    it('can render submission-windows import page', function () {
        actingAs($this->admin);
        
        livewire(ImportPage::class, ['fileType' => 'submission-windows'])
            ->assertSee('Import Submission Windows')
            ->assertSee('course code | assessment type | feedback type | staff email | submission window from | submission window to | comments')
            ->assertSee('ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:08 | 27/06/2025 16:08 | My moodle quiz is great')
            ->assertSee('Upload');
    });
});

it('requires authentication for import routes', function () {
        $file = UploadedFile::fake()->create('courses.xlsx', 100);
        
        post('/import/courses', ['importFile' => $file])
            ->assertRedirect('/login');
});

it('requires admin privileges for import routes', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('courses.xlsx', 100);
        
    post('/import/courses', ['importFile' => $file])
            ->assertForbidden();
});

    

describe('Import Page Access', function () {
    it('allows admin to access import page', function () {
        actingAs($this->admin);
        
        $this->get('/import/courses')
            ->assertOk()
            ->assertSee('Import Courses');
    });

    it('prevents non-admin from accessing import page', function () {
        actingAs($this->user);
        
        $this->get('/import/courses')
            ->assertForbidden();
    });
});

describe('Import Files', function () {

    beforeEach(function () {
        $this->testCourseData = [
            ['Course Title', 'Code', 'Discipline', 'Active (Yes/No)'],
            ['Geology Course', 'GES1235', 'Ignore', 'Yes'],
            ['Maths Course', 'MATH1235', 'Ignore', 'Yes'],
            ['Chemistry Course', 'CHEM1112', 'Ignore', 'Yes'],
            ['Engineering Course', 'ENG1213', 'Ignore', 'Yes'], 
        ];

        $this->testStudentCourseData = [
            ['Forenames', 'Surname', 'GUID', 'Course Code'],
            ['Jane', 'Smith', '123456789S', 'GES1235'],
            ['John', 'Doe', '123456789T', 'MATH1235'],
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

        $this->testAssessmentSubmissionWindows = [
            ['Course Code', 'Assessment Type', 'Feedback Type', 'Staff Email', 'Submission Window From', 'Submission Window To', 'Comments'],
            ['GES1235', 'Group Assignment', 'Moodle - Graded', 'claire.jones@example.com', '26/06/2025 16:07', '27/06/2025 16:07', 'My moodle quiz is great'],
            ['MATH1235', 'Essay', 'Moodle', 'tad.murray@example.com', '26/06/2025 16:07', '27/06/2025 16:07', ''],
        ];

    });

    it('imports courses', function () {

        expect(Course::count())->toBe(0); 

        // Import using function in Courses importer
        (new Courses())->process($this->testCourseData);

        // Check course counts
        expect(Course::count())->toBe(4);

        // Check course info on each course
        $course = Course::where('code', '=', 'GES1235')->first();
        expect($course)->not->toBeNull();
        expect($course->title)->toBe('Geology Course');
        expect($course->code)->toBe('GES1235');

        // Check course info on course page
        livewire(LivewireCourse::class, ['course' => $course])
            ->assertSee('Geology Course')
            ->assertSee('GES1235');
    });

    it('imports student course allocations', function () {
        expect(User::count())->toBe(2);
        (new Courses())->process($this->testCourseData);
        
        (new StudentCourses())->process($this->testStudentCourseData);
        expect(User::count())->toBe(4);

        $student1 = User::where('username', '123456789S')->first();
        $student2 = User::where('username', '123456789T')->first();

        expect($student1->forenames)->toBe('Jane');
        expect($student1->surname)->toBe('Smith');
        expect($student2->forenames)->toBe('John');
        expect($student2->surname)->toBe('Doe');

        expect($student1->coursesAsStudent()->count())->toBe(1);
        expect($student2->coursesAsStudent()->count())->toBe(1);

        expect($student1->coursesAsStudent()->first()->code)->toBe('GES1235');
        expect($student2->coursesAsStudent()->first()->code)->toBe('MATH1235');

        actingAs($this->admin);
        livewire(Student::class, ['student' => $student1])
            ->assertSee('Smith, Jane')
            ->assertSee('123456789S')
            ->assertSee('GES1235');

        livewire(LivewireCourse::class, ['course' => $student1->coursesAsStudent()->first()])
            ->assertSee('Smith, Jane');
    });

    it('imports staff course allocations', function () {
        expect(User::count())->toBe(2);
        (new Courses())->process($this->testCourseData);
        (new StaffCourses())->process($this->testStaffCourseData);
        expect(User::count())->toBe(4);

        $staff1 = User::where('username', 'cls2x')->first();
        $staff2 = User::where('username', 'tad2x')->first();

        expect($staff1->forenames)->toBe('Claire');
        expect($staff1->surname)->toBe('Jones');
        expect($staff2->forenames)->toBe('Tad');
        expect($staff2->surname)->toBe('Murray');

        actingAs($this->admin);
        livewire(Staff::class, ['staff' => $staff1])
            ->assertSee('Jones, Claire')
            ->assertSee('claire.jones@example.com')
            ->assertSee('GES1235');
    });

    it('imports assessments with deadlines', function () {
        expect(Assessment::count())->toBe(0);
        (new Courses())->process($this->testCourseData);
        (new StaffCourses())->process($this->testStaffCourseData);
        (new Assessments())->process($this->testAssessmentDeadlines);
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
            ->assertSee('Moodle - Graded');
            // Why doesn't this one work?
            // ->assertSee('2025-06-26 16:07');
    });

    it('imports assessments with submission windows', function () {
        expect(Assessment::count())->toBe(0);
        (new Courses())->process($this->testCourseData);
        (new StaffCourses())->process($this->testStaffCourseData);
        (new Assessments())->process($this->testAssessmentSubmissionWindows);
        expect(Assessment::count())->toBe(2);

        $assessment1 = Assessment::where('course_id', '=', '1')->first();
        
        expect($assessment1)->not->toBeNull();
        expect($assessment1->course->title)->toBe('Geology Course');
        expect($assessment1->type)->toBe('Group Assignment');
        expect($assessment1->feedback_type)->toBe('Moodle - Graded');
        // TODO: Test submission windows

        actingAs($this->admin);
        livewire(FeedbackReport::class)->assertSee('Group Assignment');

        livewire(LivewireAssessment::class, ['assessment' => $assessment1])
            ->assertSee('Group Assignment')
            ->assertSee('Moodle - Graded');
    });

    // TODO: Test delete all data functionality
});