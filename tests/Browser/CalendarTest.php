<?php
namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CalendarTest extends DuskTestCase
{
    public function testCalendar()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/home')
                ->assertSee('All years');
        });
    }
}