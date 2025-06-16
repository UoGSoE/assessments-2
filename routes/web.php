<?php

use App\Livewire\Home;
use App\Livewire\Staff;
use App\Livewire\Course;
use App\Livewire\Student;
use App\Livewire\StaffList;
use App\Livewire\Assessment;
use App\Livewire\FeedbackReport;
use App\Livewire\CreateAssessment;
use App\Livewire\EditAssessment;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('/report/feedback', FeedbackReport::class)->name('assessment.index');

Route::get('/assessment/create', CreateAssessment::class)->name('assessment.create');

Route::get('/assessment/edit/{id}', EditAssessment::class)->name('assessment.edit');

Route::get('/assessment/{id}', Assessment::class)->name('assessment.show');


Route::get('/course/{id}', Course::class)->name('course.show');


Route::get('/staff/{id}', Staff::class)->name('staff.show');

Route::get('/report/staff', StaffList::class)->name('staff.index');

Route::get('/student/{id}', Student::class)->name('student.show');


