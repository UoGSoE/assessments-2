<?php

use App\Http\Controllers\ImportController;
use App\Livewire\Assessment;
use App\Livewire\Course;
use App\Livewire\CreateAssessment;
use App\Livewire\EditAssessment;
use App\Livewire\FeedbackReport;
use App\Livewire\Home;
use App\Livewire\ImportCoursePage;
use App\Livewire\ImportDeadlinePage;
use App\Livewire\ImportStaffAllocationPage;
use App\Livewire\ImportStudentAllocationPage;
use App\Livewire\ImportSubmissionWindowPage;
use App\Livewire\LoginLogPage;
use App\Livewire\Staff;
use App\Livewire\StaffList;
use App\Livewire\StatisticsPage;
use App\Livewire\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', [\App\Http\Controllers\Auth\SSOController::class, 'login'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\SSOController::class, 'doLocalLogin'])->name('login.do');
Route::get('/auth/callback', [\App\Http\Controllers\Auth\SSOController::class, 'handleProviderCallback'])->name('sso.callback');

Route::post('/logout', function () {
    Auth::logout();

    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/report/feedback', FeedbackReport::class)->name('assessment.index');

        Route::get('/assessment/create', CreateAssessment::class)->name('assessment.create');

        Route::get('/assessment/edit/{id}', EditAssessment::class)->name('assessment.edit');

        Route::get('/staff/{staff}', Staff::class)->name('staff.show');

        Route::get('/report/staff', StaffList::class)->name('staff.index');

        Route::get('/statistics', StatisticsPage::class)->name('statistics');

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

    Route::get('/', Home::class)->name('home');

    Route::get('/assessment/{assessment}', Assessment::class)->name('assessment.show')->middleware('can:view-assessment,assessment');

    Route::get('/course/{course}', Course::class)->name('course.show')->middleware('can:view-course,course');

    Route::get('/student/{student}', Student::class)->name('student.show')->middleware('can:staff-admin-access');

});
