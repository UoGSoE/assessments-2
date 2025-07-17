<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class ImportStudentAllocationPage extends Component
{
    public $errors;

    public function render()
    {
        return view('livewire.import-student-allocation-page');
    }
}
