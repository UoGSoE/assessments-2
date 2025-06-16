<div>
    <flux:heading size="xl" class="mb-4">Course Details</flux:heading>
    <div class="flex flex-col gap-4"> 
        <div>
            <flux:heading>Title</flux:heading>
            <flux:text>{{ $course->title }}</flux:text>
        </div>
        <div>
            <flux:heading>Code</flux:heading>
            <flux:text>{{ $course->code }}</flux:text>
        </div>
    </div>
    <flux:separator class="mt-4 mb-4" />
    <div class="flex flex-row justify-between">
        <div>
            <flux:heading>Students</flux:heading>
            <ul class="list-disc">
            @foreach ($students as $student)
                <li><flux:text><a class="text-blue-500" href="{{ route('student.show', $student->id) }}">{{ $student->name }}</a></flux:text></li>
            @endforeach
            </ul>
        </div>
        <div>
            <flux:heading>Assessments</flux:heading>
            <ul class="list-disc">
            @foreach ($assessments as $assessment)
                <li><flux:text><a class="text-blue-500" href="{{ route('assessment.show', $assessment->id) }}">{{ $assessment->type }}</a> - {{$assessment->deadline}}</flux:text></li>
            @endforeach
            </ul>
        </div>
    </div>
</div>