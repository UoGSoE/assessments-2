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
    <div id='calendar'></div>
</div>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
<script>

document.addEventListener('livewire:initialized', function() {
    
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        events: @json($assessmentsArray),
        initialView: 'dayGridMonth',
        displayEventTime: false,
        eventDisplay: 'block',
        
    });
    calendar.render();
});

</script>
@endpush
