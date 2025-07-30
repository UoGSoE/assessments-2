<?php

use App\Importers\Courses;
use App\Importers\StaffCourses;
use App\Livewire\ImportStaffAllocationPage;
use App\Livewire\Staff;
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
    $this->incorrectlyFormattedStaffCourseData = [
        ['Surname', 'Forenames', 'GUID', 'Email', 'Course Code'],
        ['Jones', 'Claire', 'cls2x', 'claire.jones@example.com', 'GES1235'],
        ['Murray', 'Tad', 'tad2x', 'tad.murray@example.com', 'MATH1235'],
    ];
    $this->missingStaffCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Email', 'Course Code'],
        ['Claire', 'Jones', '', 'claire.jones@example.com', 'GES1235'],
        ['Tad', 'Murray', 'tad2x', '', 'MATH1235'],
    ];
    $this->nonexistentCourseData = [
        ['Forenames', 'Surname', 'GUID', 'Email', 'Course Code'],
        ['Claire', 'Jones', 'cls2x', 'claire.jones@example.com', 'GES1234'],
        ['Tad', 'Murray', 'tad2x', 'tad.murray@example.com', 'MATH1234'],
    ];
});

it('can render staff-courses import page', function () {
    actingAs($this->admin);
    
    livewire(ImportStaffAllocationPage::class)
        ->assertSee('Import Staff Course Allocations')
        ->assertSee('Please ensure all courses are uploaded to the database first.')
        ->assertSee('Forenames | Surname | GUID | Email | Course Code')
        ->assertSee('Claire | Jones | cls2x | claire.jones@example.com | ENG1000')
        ->assertSee('Upload');
});

it('allows admin to access import page', function () {
    actingAs($this->admin);
    
    $this->get('/import/staff-courses')
        ->assertOk()
        ->assertSee('Import Staff Course Allocations');
});

it('prevents non-admin from accessing import page', function () {
    actingAs($this->user);
    
    $this->get('/import/staff-courses')
        ->assertForbidden();
});

it('requires admin privileges to import staff course allocations', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('staff-courses.xlsx', 100);
        
    post('/import/staff-courses', ['importFile' => $file])
            ->assertForbidden();
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

it('does not import staff course allocations file with wrong format', function () {
    $errors = (new StaffCourses())->process($this->incorrectlyFormattedStaffCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(1, $errors);
    $this->assertEquals("Incorrect file format - please check the file and try again.", $errors[0]);
});

it('handles staff course allocations with missing data', function () {
    $errors = (new StaffCourses())->process($this->missingStaffCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(2, $errors);
    $this->assertEquals("Row 2: GUID is required", $errors[0]);
    $this->assertEquals("Row 3: Email is required", $errors[1]);
});

it('handles staff course allocations with nonexistent course', function () {
    $errors = (new StaffCourses())->process($this->nonexistentCourseData);
    expect(User::count())->toBe(2);
    $this->assertCount(2, $errors);
    $this->assertEquals("Course with code 'GES1234' not found - please add it to the system first.", $errors[0]);
    $this->assertEquals("Course with code 'MATH1234' not found - please add it to the system first.", $errors[1]);
});
