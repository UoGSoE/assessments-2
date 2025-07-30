<?php

use App\Importers\Courses;
use App\Livewire\Course as LivewireCourse;
use App\Livewire\ImportCoursePage;
use App\Models\Course;
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
    $this->incorrectlyFormattedCourseData = [
        ['Code', 'Course Title', 'Discipline', 'Active (Yes/No)'],
        ['Geology Course', 'GES1235', 'Ignore', 'Yes'],
        ['Maths Course', 'MATH1235', 'Ignore', 'Yes'],
        ['Chemistry Course', 'CHEM1112', 'Ignore', 'Yes'],
        ['Engineering Course', 'ENG1213', 'Ignore', 'Yes'], 
    ];
    $this->missingCourseData = [
        ['Course Title', 'Code', 'Discipline', 'Active (Yes/No)'],
        ['Geology Course', 'GES1235', 'Ignore', 'Yes'],
        ['Maths Course', '', 'Ignore', 'Yes'],
        ['Chemistry Course', 'CHEM1112', 'Ignore', 'Yes'],
        ['', 'ENG1213', 'Ignore', 'Yes'], 
    ];
});

it('can render courses import page', function () {
    actingAs($this->admin);
    
    livewire(ImportCoursePage::class)
        ->assertSee('Import Courses')
        ->assertSee('Course Title | Code | Discipline | Active (Yes/No)')
        ->assertSee('Aero Engineering | ENG4037 | Aero | Yes')
        ->assertSee('Upload');
});

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

it('requires admin privileges to import courses', function () {
    actingAs($this->user);
    $file = UploadedFile::fake()->create('courses.xlsx', 100);
        
    post('/import/courses', ['importFile' => $file])
            ->assertForbidden();
});

it('imports courses', function () {

    expect(Course::count())->toBe(0); 

    (new Courses())->process($this->testCourseData);

    expect(Course::count())->toBe(4);

    $course = Course::where('code', '=', 'GES1235')->first();
    expect($course)->not->toBeNull();
    expect($course->title)->toBe('Geology Course');
    expect($course->code)->toBe('GES1235');

    livewire(LivewireCourse::class, ['course' => $course])
        ->assertSee('Geology Course')
        ->assertSee('GES1235');
});

it('does not import courses file with wrong format', function () {
    $errors = (new Courses())->process($this->incorrectlyFormattedCourseData);
    expect(Course::count())->toBe(0);
    $this->assertCount(1, $errors);
    $this->assertEquals("Incorrect file format - please check the file and try again.", $errors[0]);
});

it('handles courses with missing data', function () {
    $errors = (new Courses())->process($this->missingCourseData);
    expect(Course::count())->toBe(2);
    $this->assertCount(2, $errors);
    $this->assertEquals("Row 3: Course code is required", $errors[0]);
    $this->assertEquals("Row 5: Course title is required", $errors[1]);
});