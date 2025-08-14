<div>
    <div class="flex flex-row flex-wrap gap-4">

        <flux:date-picker wire:model.live="minDate">
            <x-slot name="trigger">
                <flux:date-picker.input label="Min Date:" />
            </x-slot>
        </flux:date-picker>

        <flux:date-picker wire:model.live="maxDate">
            <x-slot name="trigger">
                <flux:date-picker.input label="Max Date:" />
            </x-slot>
        </flux:date-picker>

        <flux:select wire:model.live="userType" label="User Type:">
            <flux:select.option value="all">All</flux:select.option>
            <flux:select.option value="staff">Staff</flux:select.option>
            <flux:select.option value="student">Student</flux:select.option>
            <flux:select.option value="admin">Admin</flux:select.option>
        </flux:select>

    </div>
    <flux:button wire:click="clearFilters"
        class="mt-4 px-4 py-2 bg-gray-500 text-black rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
        Clear Filters
    </flux:button>
    <div class="flex flex-row gap-4 items-center w-full mt-4">
        <flux:text>Search</flux:text>
        <flux:input wire:model.live.debounce="searchText" size="sm" class="flex-1 w-full" />
    </div>
    <flux:text class="mb-4 mt-4 text-sm text-gray-600">
        Showing {{ $loginLogs->count() }} login log{{ $loginLogs->count() !== 1 ? 's' : '' }}
    </flux:text>

    @if ($loginLogs->count() > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Username</flux:table.column>
                <flux:table.column>User Type</flux:table.column>
                <flux:table.column>Login Time</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($loginLogs as $loginLog)
                    <flux:table.row :key="$loginLog->id">
                        <flux:table.cell class="flex items-center gap-3">
                            {{ $loginLog->user->name }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $loginLog->user->username }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($loginLog->user->is_admin)
                                Admin
                            @elseif ($loginLog->user->is_staff)
                                Staff
                            @else
                                Student
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $loginLog->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:text class="mb-4 mt-4 text-sm text-gray-600">
            No login logs found
        </flux:text>
    @endif
</div>
