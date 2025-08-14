<?php

namespace App\Livewire;

use App\Models\LoginLog;
use Livewire\Component;

class StatisticsPage extends Component
{
    public array $data = [];

    public function mount()
    {
        $this->data = $this->fetchData();
    }

    public function render()
    {
        return view('livewire.statistics-page');
    }

    public function fetchData()
    {
        $data = [];

        for ($i = 12; $i >= 1; $i--) {
            $startDate = now()->subMonths($i);
            $endDate = now()->subMonths($i - 1);

            $studentLogins = $this->getLoginCount($startDate, $endDate, false);
            $staffLogins = $this->getLoginCount($startDate, $endDate, true);

            $data[] = [
                'month' => $startDate->format('F Y'),
                'studentLogins' => $studentLogins,
                'staffLogins' => $staffLogins,
            ];
        }

        return $data;
    }

    private function getLoginCount($startDate, $endDate, $isStaff = false)
    {
        $query = LoginLog::join('users', 'login_logs.user_id', '=', 'users.id')
            ->whereBetween('login_logs.created_at', [$startDate, $endDate]);

        if ($isStaff) {
            $query->where('users.is_staff', true);
        } else {
            $query->where('users.is_staff', false)
                ->where('users.is_admin', false);
        }

        return $query->count();
    }
}
