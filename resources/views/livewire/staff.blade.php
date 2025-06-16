<div>
    <flux:heading size="xl" class="mb-4">Staff Details</flux:heading>
    <div class="flex flex-col gap-4"> 
        <div>
            <flux:heading>Name</flux:heading>
            <flux:text>{{ $staff->getNameAttribute() }}</flux:text>
        </div>
        <div>
            <flux:heading>Email</flux:heading>
            <flux:text>{{ $staff->email }}</flux:text>
        </div>
        <div>
            <flux:heading>Courses</flux:heading>
            <flux:text>
                @if (! $courses->isEmpty())
                @foreach ($courses as $course)
                    <a href="{{ route('course.show', $course->id) }}"><span class="text-blue-500">{{ $course->code }}</span></a>
                @endforeach
                @else
                    <flux:text>No courses found</flux:text>
                @endif
            </flux:text>
        </div>
    </div>
    <flux:separator class="mt-4 mb-4" />
    <flux:heading size="xl" class="mb-4">Assessments</flux:heading>
    @if (! $assessments->isEmpty())
    <flux:table>
    <flux:table.columns>
        <flux:table.column>Course</flux:table.column>
        <flux:table.column>Type</flux:table.column>
        <flux:table.column>Feedback Deadline</flux:table.column>
        <flux:table.column>Feedback Completed Date</flux:table.column>
        <flux:table.column>Complaints</flux:table.column>
    </flux:table.columns>
    <flux:table.rows>
        @foreach ($assessments as $assessment)
        <flux:table.row>
            <flux:table.cell><a class="text-blue-500" href="{{ route('assessment.show', $assessment->id) }}">{{ $assessment->course->code }}</a> {{ $assessment->course->title }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->type }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->feedback_deadline }}</flux:table.cell>
            <flux:table.cell>{{ $assessment->feedback_completed_date }}</flux:table.cell>
            <flux:table.cell>{{ count($assessment->complaints) }}</flux:table.cell>
        </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
@else
    <flux:text>No assessments found</flux:text>
@endif
</div>
