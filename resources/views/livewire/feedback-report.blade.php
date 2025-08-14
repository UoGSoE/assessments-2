<div>

    <div class="flex flex-row justify-between items-baseline">
        <flux:heading size="xl" class="mb-4">Feedback Report</flux:heading>
        <div class="flex flex-row gap-2">
            <flux:modal.trigger name="removeAllDataModal">
                <flux:button variant="danger" icon="trash"></flux:button>
            </flux:modal.trigger>
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down">More</flux:button>
                <flux:menu>
                    <flux:menu.item icon="plus"><a href="{{ route('assessment.create') }}">Add new assessment</a>
                    </flux:menu.item>
                    <flux:menu.item icon="arrow-down-tray" wire:click="exportAsExcel">Export as Excel</flux:menu.item>
                    <flux:menu.item icon="user"><a href="{{ route('staff.index') }}">Staff Report</a>
                    </flux:menu.item>
                    <flux:menu.separator />
                    <a href="{{ route('import.courses.show') }}">
                        <flux:menu.item icon="arrow-up-tray">1. Import courses</flux:menu.item>
                    </a>
                    <flux:modal.trigger name="removeAllStudentCoursesModal">
                        <flux:menu.item icon="arrow-up-tray">2. Remove all students' courses</flux:menu.item>
                    </flux:modal.trigger>
                    <a href="{{ route('import.student-courses.show') }}">
                        <flux:menu.item icon="arrow-up-tray">3. Import student allocations</flux:menu.item>
                    </a>
                    <a href="{{ route('import.staff-courses.show') }}">
                        <flux:menu.item icon="arrow-up-tray">4. Import staff allocations</flux:menu.item>
                    </a>
                    <a href="{{ route('import.deadlines.show') }}">
                        <flux:menu.item icon="arrow-up-tray">5. Import deadlines</flux:menu.item>
                    </a>
                    <a href="{{ route('import.submission-windows.show') }}">
                        <flux:menu.item icon="arrow-up-tray">6. Import submission windows</flux:menu.item>
                    </a>
                    <flux:menu.separator />
                    <a href="{{ route('login-logs') }}">
                        <flux:menu.item icon="list-bullet">Login logs</flux:menu.item>
                    </a>
                    <a href="{{ route('statistics') }}">
                        <flux:menu.item icon="list-bullet">Login statistics</flux:menu.item>
                    </a>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <div class="flex flex-row items-center w-full">
        <flux:text class="mr-2">Search</flux:text>
        <flux:input wire:model.live.debounce="searchText" size="sm" class="w-full flex-1" />
    </div>

    <flux:select wire:model.live="school" class="mt-4">
        <flux:select.option value="all">All schools</flux:select.option>
        <flux:select.option value="ENG">ENG</flux:select.option>
        <flux:select.option value="PHAS">PHAS</flux:select.option>
        <flux:select.option value="MATH">MATH</flux:select.option>
        <flux:select.option value="CHEM">CHEM</flux:select.option>
        <flux:select.option value="GES">GES</flux:select.option>
        <flux:select.option value="COMP">COMP</flux:select.option>
    </flux:select>


    @if ($assessments->count() > 0)
        <flux:table class="table-fixed">
            <flux:table.columns>
                <flux:table.column>Course</flux:table.column>
                <flux:table.column>Level</flux:table.column>
                <flux:table.column>Assessment Type</flux:table.column>
                <flux:table.column>Feedback Type</flux:table.column>
                <flux:table.column>Staff</flux:table.column>
                <flux:table.column>Deadline</flux:table.column>
                <flux:table.column>Feedback Deadline</flux:table.column>
                <flux:table.column>Feedback Completed Date</flux:table.column>
                <flux:table.column>Complaints</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($assessments as $assessment)
                    <flux:table.row>
                        <flux:table.cell><a class="text-blue-500"
                                href="{{ route('assessment.show', $assessment->id) }}">{{ $assessment->course->code }}</a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $assessment->course->year }}</flux:table.cell>
                        <flux:table.cell>{{ $assessment->type }}</flux:table.cell>
                        <flux:table.cell>{{ $assessment->feedback_type }}</flux:table.cell>
                        <flux:table.cell><a class="text-blue-500"
                                href="{{ route('staff.show', $assessment->staff->id) }}">{{ $assessment->staff->name }}</a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $assessment->deadline ? $assessment->deadline->format('d/m/Y') : '' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $assessment->feedback_deadline ? $assessment->feedback_deadline->format('d/m/Y') : '' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $assessment->feedback_completed_date ? $assessment->feedback_completed_date->format('d/m/Y') : '' }}
                        </flux:table.cell>
                        <flux:table.cell>{{ count($assessment->complaints) }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:heading size="lg" class="mb-4 mt-4">No assessments found.</flux:heading>
    @endif

    <flux:modal name="removeAllStudentCoursesModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove All Students' Courses</flux:heading>
                <flux:text class="mt-2">
                    <p>
                        Would you like to remove all students from their courses?<br>
                        <span class="text-red-500">This action cannot be undone.</span>
                    </p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="removeAllStudentCourses">Remove all students'
                    courses</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="removeAllDataModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove All Data</flux:heading>
                <flux:text class="mt-2">
                    <p>
                        Would you like to remove all assessments and complaints?<br>
                        <span class="text-red-500">This action cannot be undone.</span>
                    </p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="deleteAllData">Remove all assessments and
                    complaints</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
