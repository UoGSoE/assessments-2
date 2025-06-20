<?php

use App\Livewire\Course;
use App\Livewire\FeedbackReport;
use App\Livewire\StaffList;
use App\Models\Assessment;
use App\Models\Complaint;
use App\Models\Course as ModelsCourse;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    livewire(StaffList::class)
        ->assertSee('Staff Report')
        ->assertSee('Search')
        ->assertSee('Name')
        ->assertSee('Assessments')
        ->assertSee('Student Feedback')
        ->assertSee('Missed Deadlines');
});

it('displays staff members', function () {
    $staff1 = User::factory()->staff()->create();
    $staff2 = User::factory()->staff()->create();
    $assessment1 = Assessment::factory()->create(['staff_id' => $staff1->id]);
    $assessment2 = Assessment::factory()->create(['staff_id' => $staff1->id]);
    $complaint1 = Complaint::factory()->create(['assessment_id' => $assessment1->id]);

    livewire(StaffList::class)
        ->assertSee($staff1->name)
        ->assertSee(2)
        ->assertSee(1)
        ->assertSee($staff2->name)
        ->assertSee(0)
        ->assertSee(0);
});

it('searches for a staff member by name', function () {
    $staff1 = User::factory()->staff()->create(['surname' => 'Smith']);
    $staff2 = User::factory()->staff()->create(['surname' => 'Johnson']);
    $staff3 = User::factory()->staff()->create(['surname' => 'Williams']);

    livewire(StaffList::class)
        ->assertSee($staff1->name)
        ->assertSee($staff2->name)
        ->assertSee($staff3->name)
        ->set('searchText', 'Smith')
        ->assertSee($staff1->name)
        ->assertDontSee($staff2->name)
        ->assertDontSee($staff3->name);
});

it('displays the number of missed deadlines', function () {
    $staff = User::factory()->staff()->create();
    $assessment1 = Assessment::factory()->create(['staff_id' => $staff->id, 'feedback_deadline' => now()->subDays(1)]);
    $assessment2 = Assessment::factory()->create(['staff_id' => $staff->id, 'feedback_deadline' => now()->subDays(1)]);

    livewire(StaffList::class)
        ->assertSee(2);
});

it('only allows admins to view page', function () {
    $random_student = User::factory()->create();
    $random_staff = User::factory()->staff()->create();

    actingAs($random_student)->get(route('staff.index'))
        ->assertForbidden();

    actingAs($random_staff)->get(route('staff.index'))
        ->assertForbidden();

    actingAs($this->admin)->get(route('staff.index'))
        ->assertOk();
});