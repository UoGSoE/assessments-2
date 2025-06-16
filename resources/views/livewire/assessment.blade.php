<div>
    <div class="flex flex-row justify-between">
        <div class="flex flex-row gap-2">
            <flux:heading size="xl" class="mb-4">Assessment Details</flux:heading>
            <flux:button icon="pencil-square" href="{{ route('assessment.edit', $assessment->id) }}"></flux:button>
        </div>
        <div>
            <flux:button variant="danger" icon="trash" wire:click="deleteAssessment"></flux:button>
        </div>
    </div>
    <div class="flex flex-col gap-4"> 
        <div>
        <flux:heading>Course</flux:heading>
        <flux:text><a class="text-blue-500" href="{{ route('course.show', $assessment->course->id) }}">{{ $assessment->course->code }}</a> {{ $assessment->course->title }}</flux:text>
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
            <flux:date-picker wire:model="feedback_completed_date" />
            <flux:error name="feedback_deadline" />
        </flux:field>
        <flux:button type="submit">Save Completed Date</flux:button>
        </form>
        @endif
        </div>
    </div>
    <flux:separator class="mt-4 mb-4" />
    <flux:heading size="xl">Complaints Left</flux:heading>
    @foreach ($complaints as $complaint)
        <div>
            <flux:heading>{{ $complaint->created_at }}</flux:heading>
            <flux:text>{{ $complaint->student->name }}</flux:text>
        </div>
    @endforeach
</div>
