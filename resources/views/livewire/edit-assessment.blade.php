<div>
    <flux:heading size="xl" class="mb-4">Edit Assessment</flux:heading>
    <form wire:submit="updateAssessment">
        <flux:field class="mb-4">
            <flux:label>Assessment Type</flux:label>
            <flux:input wire:model="assessment_type" />
            <flux:error name="assessment_type" />
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Staff Feedback Type</flux:label>
            <flux:input wire:model="staff_feedback_type" />
            <flux:error name="staff_feedback_type" />
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Staff</flux:label>
            <flux:select wire:model="staff_id">
                @foreach ($staff as $staffMember)
                    <flux:select.option value="{{ $staffMember->id }}"
                        :selected="$staffMember->id === $assessment->staff_id">{{ $staffMember->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="staff_id" />
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Course</flux:label>
            <flux:select wire:model="course_id">
                @foreach ($courses as $course)
                    <flux:select.option value="{{ $course->id }}" :selected="$course->id === $assessment->course_id">
                        {{ $course->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="course_id" />
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Deadline</flux:label>
            <flux:date-picker wire:model="deadline" />
            <flux:error name="deadline" />
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Feedback Deadline</flux:label>
            <flux:date-picker wire:model="feedback_deadline" />
            <flux:error name="feedback_deadline" />
        </flux:field>
        <flux:separator class="mt-4 mb-4" />
        <flux:field class="mb-4">
            <flux:label>Comment</flux:label>
            <flux:textarea wire:model="comment" />
            <flux:error name="comment" />
        </flux:field>
        <flux:button type="submit">Update</flux:button>
    </form>
</div>
