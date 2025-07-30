<?php

use App\Http\Controllers\ImportController;
use App\Livewire\Home;
use App\Livewire\Staff;
use App\Livewire\Course;
use App\Livewire\Student;
use App\Livewire\StaffList;
use App\Livewire\Assessment;
use App\Livewire\EditAssessment;
use App\Livewire\FeedbackReport;
use App\Livewire\CreateAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\LdapLogin as LdapLogin;
use App\Livewire\ImportCoursePage;
use App\Livewire\ImportDeadlinePage;
use App\Livewire\ImportPage;
use App\Livewire\ImportStaffAllocationPage;
use App\Livewire\ImportStaffPage;
use App\Livewire\ImportStudentAllocationPage;
use App\Livewire\ImportSubmissionWindowPage;
use App\Livewire\LoginLogPage;

Route::get('/login', LdapLogin::class)->name('login');

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');



Route::middleware(['auth', 'can:is-admin'])->group(function () {
    Route::get('/report/feedback', FeedbackReport::class)->name('assessment.index');

    Route::get('/report/feedback', FeedbackReport::class)->name('assessment.index');

    Route::get('/assessment/create', CreateAssessment::class)->name('assessment.create');

    Route::get('/assessment/edit/{id}', EditAssessment::class)->name('assessment.edit');

    Route::get('/staff/{staff}', Staff::class)->name('staff.show');

    Route::get('/report/staff', StaffList::class)->name('staff.index');

    Route::get('/import/courses', ImportCoursePage::class)->name('import.courses.show');
    Route::get('/import/student-courses', ImportStudentAllocationPage::class)->name('import.student-courses.show');
    Route::get('/import/staff-courses', ImportStaffAllocationPage::class)->name('import.staff-courses.show');
    Route::get('/import/deadlines', ImportDeadlinePage::class)->name('import.deadlines.show');
    Route::get('/import/submission-windows', ImportSubmissionWindowPage::class)->name('import.submission-windows.show');
    Route::post('/import/{type}', [ImportController::class, 'import'])
        ->where('type', 'courses|student-courses|staff-courses|deadlines|submission-windows')
        ->name('import.upload');

    Route::get('/login-logs', LoginLogPage::class)->name('login-logs');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', Home::class)->name('home');

    Route::get('/assessment/{assessment}', Assessment::class)->name('assessment.show')->middleware('can:view-assessment,assessment');

    Route::get('/course/{course}', Course::class)->name('course.show')->middleware('can:view-course,course');

    Route::get('/student/{student}', Student::class)->name('student.show')->middleware('can:view-student,student');
});
