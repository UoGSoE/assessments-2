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
                    <a href="{{ route('course.show', $course->id) }}"><span
                            class="text-blue-500">{{ $course->code }}</span></a>
                @endforeach
            </flux:text>
        </div>
    </div>
    <flux:separator class="mt-4 mb-4" />
    <flux:heading size="xl" class="mb-4">Assessments for {{ $student->getNameAttribute() }}</flux:heading>
    <div wire:ignore>
        <div id='calendar'></div>
    </div>
    <div x-init="init()" x-data="{
        calendar: null,
        events: $wire.entangle('assessmentsArray'),
    
        initCalendar() {
    
            const calendarEl = document.getElementById('calendar');
            console.log('Calendar element:', calendarEl);
    
            this.calendar = new window.FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                plugins: [window.FullCalendar.dayGridPlugin],
                events: this.events,
                displayEventTime: true,
                eventDisplay: 'block',
                height: 'auto'
            });
    
            this.calendar.render();
    
            this.$watch('events', () => {
                if (this.calendar) {
                    this.calendar.destroy();
                }
                this.initCalendar();
            });
        },
        init() {
            this.$nextTick(() => {
                this.initCalendar();
            });
        }
    }">
    </div>
</div>
