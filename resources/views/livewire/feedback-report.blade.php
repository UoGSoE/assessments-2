<div>
    <div class="flex flex-row justify-between">
        <flux:heading size="xl" class="mb-4">Feedback Report</flux:heading>
        <div class="flex flex-row gap-2">
            <flux:button variant="danger" icon="trash" wire:click="deleteAllData"></flux:button>
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down">More</flux:button>
                <flux:menu>
                    <flux:menu.item icon="plus"><a href="{{ route('assessment.create') }}">Add new assessment</a></flux:menu.item>
                    <flux:menu.item icon="arrow-down-tray">Export as Excel</flux:menu.item>
                    <flux:menu.item icon="user"><a href="{{ route('staff.index') }}">Staff Report</a></flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-up-tray">1. Import courses</flux:menu.item>
                    <flux:menu.item icon="arrow-up-tray">2. Remove all students' courses</flux:menu.item>
                    <flux:menu.item icon="arrow-up-tray">3. Import student allocations</flux:menu.item>
                    <flux:menu.item icon="arrow-up-tray">4. Import staff allocations</flux:menu.item>
                    <flux:menu.item icon="arrow-up-tray">5. Import deadlines</flux:menu.item>
                    <flux:menu.item icon="arrow-up-tray">6. Import submission windows</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="list-bullet">Login logs</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
    <div class="flex flex-row">
        <flux:text class="mr-2">Search</flux:text>
        <flux:input wire:model.live.debounce="searchText" size="sm" class="w-1/2" />
    </div>
    @if ($assessments->count() > 0)
    <flux:table>
    <flux:table.columns>
        <flux:table.column>Course</flux:table.column>
        <flux:table.column>Level</flux:table.column>
        <flux:table.column>Assessment Type</flux:table.column>
        <flux:table.column>Feedback Type</flux:table.column>
        <flux:table.column>Staff</flux:table.column>
        <flux:table.column>Feedback Deadline</flux:table.column>
        <flux:table.column>Feedback Completed Date</flux:table.column>
        <flux:table.column>Complaints</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($assessments as $assessment)
        <flux:table.row>
            <flux:table.cell><a class="text-blue-500" href="{{ route('assessment.show', $assessment->id) }}">{{ $assessment->course->code }}</a></flux:table.cell>
            <flux:table.cell>{{ $assessment->course->year }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->type }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->feedback_type }}</flux:table.cell>
            <flux:table.cell><a class="text-blue-500" href="{{ route('staff.show', $assessment->staff->id) }}">{{ $assessment->staff->name }}</a></flux:table.cell>
            <flux:table.cell>{{ $assessment->feedback_deadline }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->feedback_completed_date }}</flux:table.cell>
            <flux:table.cell>{{ count($assessment->complaints) }}</flux:table.cell>
        </flux:table.row>
        @endforeach
        </flux:table.rows>
    </flux:table>
    @else
    <flux:heading size="lg" class="mb-4 mt-4">No assessments found.</flux:heading>
    @endif
</div>
