<?php

use App\Livewire\Home;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('can be rendered', function () {
    Carbon::setTestNow();

    actingAs($this->admin);
    livewire(Home::class)
        ->assertSee('Your Assessments')
        ->assertSee('All years');

        // TODO: How to test the calendar?
        //->assertSee('June 2025');
});