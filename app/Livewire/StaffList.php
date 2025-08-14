<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

class StaffList extends Component
{
    public $staff;

    public $searchText = '';

    public function mount()
    {
        $this->staff = User::staff()->orderBy('surname')->get();
    }

    public function render()
    {
        return view('livewire.staff-list');
    }

    public function updatedSearchText($value)
    {
        $searchTerm = $value;
        $this->staff = User::where('surname', 'like', '%'.$searchTerm.'%')
            ->orWhere('forenames', 'like', '%'.$searchTerm.'%')
            ->get();
    }

    public function exportStaffList()
    {
        $tempDir = sys_get_temp_dir();
        $fileName = now()->format('Y-m-d').'-assessments.xlsx';
        $filePath = $tempDir.'/'.$fileName;

        $writer = new \OpenSpout\Writer\XLSX\Writer;
        $writer->openToFile($filePath);

        $cells = [
            Cell::fromValue('Staff'),
            Cell::fromValue('No. Assessments'),
            Cell::fromValue('No. Student Feedbacks'),
            Cell::fromValue('No. Missed Deadlines'),
        ];

        $singleRow = new Row($cells);
        $writer->addRow($singleRow);

        foreach ($this->staff as $staffMember) {
            $cells = [
                Cell::fromValue($staffMember->name),
                Cell::fromValue($staffMember->assessments->count()),
                Cell::fromValue($staffMember->complaints->count()),
                Cell::fromValue($this->getMissedDeadlines($staffMember)),
            ];

            $singleRow = new Row($cells);
            $writer->addRow($singleRow);
        }

        $writer->close();

        return response()->download($filePath);
    }
}
