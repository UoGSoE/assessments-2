<div>
    <div class="flex flex-row justify-between items-baseline">
        <div class="flex flex-row gap-2 items-baseline">
            <flux:heading size="xl" class="mb-4">Assessment Details</flux:heading>
            @if (auth()->user()->is_admin)
                <flux:button icon="pencil-square" id="edit-assessment"
                    href="{{ route('assessment.edit', $assessment->id) }}"></flux:button>
            @endif
        </div>
        <div>
            @if (auth()->user()->is_admin)
                <flux:button variant="danger" icon="trash" id="delete-assessment" wire:click="deleteAssessment">
                </flux:button>
            @endif
        </div>
    </div>
    <div class="flex flex-col gap-4">
        <div>
            <flux:heading>Course</flux:heading>
            <flux:text><a class="text-blue-500"
                    href="{{ route('course.show', $assessment->course->id) }}">{{ $assessment->course->code }}</a>
                {{ $assessment->course->title }}</flux:text>
        </div>
        <div>
            <flux:heading>Set By</flux:heading>
            <flux:text>{{ $assessment->staff->name }}</flux:text>
        </div>
        <div>
            <flux:heading>Assessment Type</flux:heading>
            <flux:text>{{ $assessment->type }}</flux:text>
        </div>
        <div>
            <flux:heading>Feedback Will Be</flux:heading>
            <flux:text>{{ $assessment->feedback_type }}</flux:text>
        </div>
        <div>
            <flux:heading>Deadline Date</flux:heading>
            <flux:text>{{ $assessment->deadline }}</flux:text>
        </div>
        <div>
            <flux:heading>Feedback Due</flux:heading>
            <flux:text>{{ $assessment->feedback_deadline }}</flux:text>
        </div>
        <div>
            @if ($assessment->feedback_completed_date)
                <flux:heading>Feedback Completed</flux:heading>
                <flux:text>{{ $assessment->feedback_completed_date }}</flux:text>
            @else
                <form wire:submit="saveCompletedDate">
                    <flux:field class="mb-4">
                        <flux:label>Feedback Completed</flux:label>
                        @can('add-completed-date', $assessment)
                            <flux:date-picker class="mb-2" wire:model="feedback_completed_date" />
                            <flux:error name="feedback_deadline" />
                            <flux:button type="submit">Save Completed Date</flux:button>
                        @endcan
                        @cannot('add-completed-date', $assessment)
                            <flux:text>No feedback completed</flux:text>
                        @endcannot
                    </flux:field>
                </form>
            @endif
        </div>
        @if ($assessment->comment)
            <flux:heading>Comment</flux:heading>
            <flux:text>{{ $assessment->comment }}</flux:text>
        @endif
    </div>
    <flux:separator class="mt-4 mb-4" />
    @can('view-complaints', $assessment)
        <flux:heading size="xl">Feedbacks Left</flux:heading>
        @foreach ($complaints as $complaint)
            <div class="mt-4">
                <flux:heading>{{ $complaint->created_at }}</flux:heading>
                <flux:text>{{ $complaint->student->name }}</flux:text>
            </div>
        @endforeach
    @endcan
    @can('add-complaint', $assessment)
        @if ($assessment->feedback_deadline < now() && !$assessment->feedback_completed_date)
            <flux:button icon="plus" wire:click="addComplaint" class="mt-4">Report assessment feedback is overdue
            </flux:button>
        @endif
    @endcan
</div>
