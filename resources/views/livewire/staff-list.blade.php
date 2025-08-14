<div>
    <div class="flex flex-row gap-2 mb-4 items-baseline">
        <flux:heading size="xl" class="mb-4">Staff Report</flux:heading>
        <flux:button icon="arrow-down-tray" wire:click="exportStaffList"></flux:button>
    </div>
    <div class="flex flex-row gap-4 items-center w-full">
        <flux:text>Search</flux:text>
        <flux:input wire:model.live.debounce="searchText" size="sm" class="flex-1 w-full" />
    </div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Assessments</flux:table.column>
            <flux:table.column>Student Feedback</flux:table.column>
            <flux:table.column>Missed Deadlines</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($staff as $staffMember)
                <flux:table.row>
                    <flux:table.cell><a class="text-blue-500"
                            href="{{ route('staff.show', $staffMember->id) }}">{{ $staffMember->name }}</a>
                    </flux:table.cell>
                    <flux:table.cell>{{ count($staffMember->assessments) }}</flux:table.cell>
                    <flux:table.cell>{{ count($staffMember->complaints) }}</flux:table.cell>
                    <flux:table.cell>{{ $staffMember->getMissedDeadlines() }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
