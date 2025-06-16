<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class StaffList extends Component
{
    public $staff;
    public $searchText = '';

    public function mount()
    {
        $this->staff = User::where('is_student', false)->orderBy('surname')->get();
    }

    public function render()
    {
        return view('livewire.staff-list');
    }

    public function updatedSearchText($value)
    {
        $this->reset('staff');
        $searchTerm = $value;
        $this->staff = User::where('surname', 'like', '%' . $searchTerm . '%')->orWhere('forenames', 'like', '%' . $searchTerm . '%')->get();
    }
}
