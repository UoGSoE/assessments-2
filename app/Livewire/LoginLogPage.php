<?php

namespace App\Livewire;

use App\Models\LoginLog;
use Livewire\Component;
use Livewire\WithPagination;

class LoginLogPage extends Component
{
    use WithPagination;

    public $minDate = '';
    public $maxDate = '';
    public $userType = '';
    public $loginLogs;
    public $searchText = '';

    protected $queryString = [
        'minDate' => ['except' => ''],
        'maxDate' => ['except' => ''],
        'userType' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadLoginLogs();
    }

    public function render()
    {
        return view('livewire.login-log-page');
    }

    public function updatedMinDate()
    {
        $this->resetPage();
        $this->loadLoginLogs();
    }

    public function updatedMaxDate()
    {
        $this->resetPage();
        $this->loadLoginLogs();
    }

    public function updatedUserType()
    {
        $this->resetPage();
        $this->loadLoginLogs();
    }

    public function updatedSearchText($value)
    {
        $this->reset('loginLogs');
        $searchTerm = $value;
        $this->loginLogs = LoginLog::whereHas('user', function ($userQuery) use ($searchTerm) {
            $userQuery->where('surname', 'like', '%' . $searchTerm . '%')
                     ->orWhere('forenames', 'like', '%' . $searchTerm . '%');
        })->orderBy('created_at', 'desc')->get();
    }

    public function clearFilters()
    {
        $this->minDate = '';
        $this->maxDate = '';
        $this->userType = '';
        $this->resetPage();
        $this->loadLoginLogs();
    }

    private function loadLoginLogs()
    {
        $query = LoginLog::with('user');

        if ($this->minDate) {
            $query->whereDate('created_at', '>=', $this->minDate);
        }

        if ($this->maxDate) {
            $query->whereDate('created_at', '<=', $this->maxDate);
        }

        if ($this->userType == 'All') {
            $query->where('user_type', '!=', null);
        } else if ($this->userType) {
            $query->where('user_type', $this->userType);
        }

        $this->loginLogs = $query->orderBy('created_at', 'desc')->get();
    }

}
