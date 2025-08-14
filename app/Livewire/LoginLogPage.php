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

    public $searchText = '';

    protected $queryString = [
        'minDate' => ['except' => ''],
        'maxDate' => ['except' => ''],
        'userType' => ['except' => ''],
        'searchText' => ['except' => ''],
    ];

    public function render()
    {
        return view('livewire.login-log-page', [
            'loginLogs' => $this->getLoginLogs(),
        ]);
    }

    public function updatedMinDate()
    {
        $this->resetPage();
    }

    public function updatedMaxDate()
    {
        $this->resetPage();
    }

    public function updatedUserType()
    {
        $this->resetPage();
    }

    public function updatedSearchText()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->minDate = '';
        $this->maxDate = '';
        $this->userType = '';
        $this->searchText = '';
        $this->resetPage();
    }

    private function getLoginLogs()
    {
        return LoginLog::with('user')
            ->when($this->minDate, fn ($query) => $query->whereDate('created_at', '>=', $this->minDate))
            ->when($this->maxDate, fn ($query) => $query->whereDate('created_at', '<=', $this->maxDate))
            ->when($this->userType && $this->userType !== 'all', fn ($query) => $query->where('user_type', $this->userType))
            ->when($this->searchText, function ($query) {
                $searchTerm = $this->searchText;
                $query->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('surname', 'like', '%'.$searchTerm.'%')
                        ->orWhere('forenames', 'like', '%'.$searchTerm.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }
}
