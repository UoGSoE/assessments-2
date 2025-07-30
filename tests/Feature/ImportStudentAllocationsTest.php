<?php

use App\Importers\Courses;
use App\Importers\StudentCourses;
use App\Livewire\Course as LivewireCourse;
use App\Livewire\ImportStudentAllocationPage;
use App\Livewire\Student;
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
    $this->testStudentCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Course Code'],
        ['Jane', 'Smith', '123456789S', 'GES1235'],
        ['John', 'Doe', '123456789T', 'MATH1235'],
    ];
    $this->incorrectlyFormattedStudentCourseData = [
        ['Surname', 'Forenames', 'GUID', 'Course Code'],
        ['Smith', 'Jane', '123456789S', 'GES1235'],
        ['Doe', 'John', '123456789T', 'MATH1235'],
    ];
    $this->missingStudentCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Course Code'],
        ['Jane', 'Smith', '', 'GES1235'],
        ['John', 'Doe', '123456789T', ''],
    ];
    $this->nonexistentCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Course Code'],
        ['Jane', 'Smith', '123456789S', 'GES1234'],
        ['John', 'Doe', '123456789T', 'MATH1234'],
    ];
});

it('can render student-courses import page', function () {
    actingAs($this->admin);
    
    livewire(ImportStudentAllocationPage::class)
        ->assertSee('Import Student Course Allocations')
        ->assertSee('Please ensure all courses are uploaded to the database first.')
        ->assertSee('Forenames | Surname | GUID | Course Code')
        ->assertSee('Jane | Smith | 123456789S | ENG1000')
        ->assertSee('Upload');
});

it('allows admin to access import page', function () {
    actingAs($this->admin);
    
    $this->get('/import/student-courses')
        ->assertOk()
        ->assertSee('Import Student Course Allocations');
});

it('prevents non-admin from accessing import page', function () {
    actingAs($this->user);
    
    $this->get('/import/student-courses')
        ->assertForbidden();
});

it('requires admin privileges to import student course allocations', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('student-courses.xlsx', 100);
        
    post('/import/student-courses', ['importFile' => $file])
            ->assertForbidden();
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

it('does not import student course allocations file with wrong format', function () {
    $errors = (new StudentCourses())->process($this->incorrectlyFormattedStudentCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(1, $errors);
    $this->assertEquals("Incorrect file format - please check the file and try again.", $errors[0]);
});

it('handles student course allocations with missing data', function () {
    $errors = (new StudentCourses())->process($this->missingStudentCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(2, $errors);
    $this->assertEquals("Row 2: GUID is required", $errors[0]);
    $this->assertEquals("Row 3: Course code is required", $errors[1]);
});

it('handles student course allocations with nonexistent course', function () {
    $errors = (new StudentCourses())->process($this->nonexistentCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(2, $errors);
    $this->assertEquals("Course with code 'GES1234' not found - please add it to the system first.", $errors[0]);
    $this->assertEquals("Course with code 'MATH1234' not found - please add it to the system first.", $errors[1]);
});