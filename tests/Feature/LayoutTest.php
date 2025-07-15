<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->student = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
});

it('renders the page for students', function () {
    actingAs($this->student)
        ->get('/')
        ->assertSee('Assessment Calendar')
        ->assertSee($this->student->name)
        ->assertDontSee('Admin')
        ->assertSee('Your Assessments');
});

it('renders the page for admins', function () {
    actingAs($this->admin)
        ->get('/')
        ->assertSee('Assessment Calendar')
        ->assertSee($this->admin->name)
        ->assertSee('Admin')
        ->assertSee('Your Assessments');
});