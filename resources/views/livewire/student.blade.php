<div>
    <flux:heading size="xl" class="mb-4">Student Details</flux:heading>
    <div class="flex flex-col gap-4"> 
        <div>
            <flux:heading>Name</flux:heading>
            <flux:text>{{ $student->getNameAttribute() }}</flux:text>
        </div>
        <div>
            <flux:heading>Email</flux:heading>
            <flux:text>{{ $student->email }}</flux:text>
        </div>
        <div>
            <flux:heading>Courses</flux:heading>
            <flux:text>

            @foreach ($courses as $course)
                <a href="{{ route('course.show', $course->id) }}"><span class="text-blue-500">{{ $course->code }}</span></a>
            @endforeach
            </flux:text>
        </div>
    </div>
    <flux:separator class="mt-4 mb-4" />
    <flux:heading size="xl" class="mb-4">Assessments for {{ $student->getNameAttribute() }}</flux:heading>
    @foreach ($assessments as $assessment)
        <div>
            <flux:heading><a class="text-blue-500" href="{{ route('assessment.show', $assessment->id) }}">{{ $assessment->type }}</a></flux:heading>
            <flux:text>{{ $assessment->deadline }}</flux:text>
        </div>
    @endforeach
</div>
