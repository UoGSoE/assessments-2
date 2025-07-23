<div>
    <div class="flex items-baseline gap-4 justify-start">
        <flux:heading size="xl" class="mb-4 flex-1">Your Assessments</flux:heading>
        
    </div>
    <flux:select class="mb-4" wire:model.live="yearFilter">
        <flux:select.option>All years</flux:select.option>
        <flux:select.option>1st</flux:select.option>
        <flux:select.option>2nd</flux:select.option>
        <flux:select.option>3rd</flux:select.option>
        <flux:select.option>4th</flux:select.option>
        <flux:select.option>5th</flux:select.option>
    </flux:select>
    <div wire:ignore>
        <div id='calendar'></div>
    </div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
<div wire:ignore>
<div
    x-data="calendarComponent()"
    x-init="initCalendar()"
>
    <div x-ref="calendar"></div>

    <h2 class="mt-8 font-bold">List of events</h2>
    <ul class="mt-1 list-inside list-disc text-sm text-gray-500">
        <template x-for="event in events" :key="event.id">
            <li x-text="`${event.title}: ${event.start} ${(event.end ? ' through ' + event.end : '')}`"></li>
        </template>
    </ul>

    <form x-on:submit.prevent="addEvent" class="mt-8 max-w-md">
        <h2 class="font-bold">Adding a new event</h2>
        <p class="mt-1 text-sm text-gray-500">Select a date or date range on the calendar and enter a title to create a new event.</p>
        <div class="mt-4 flex items-center gap-2">
            <label for="new-event-title" class="sr-only">Event Title</label>
            <input id="new-event-title" type="text" x-model="newEventTitle" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2.5" placeholder="Event title">
            <button type="submit" class="shrink-0 rounded-md bg-white px-5 py-2.5 shadow">Submit</button>
        </div>
    </form>

    <h2 class="mt-8 font-bold">Moving events and changing duration</h2>
    <p class="mt-1 text-sm text-gray-500">Drag an event to move it to a different day. Drag the right edge to change the duration.</p>

    <h2 class="mt-8 font-bold">Deleting an event</h2>
    <p class="mt-1 text-sm text-gray-500">Click an event and confirm to remove it.</p>
</div>
</div>

</div>


@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
    <script>

    document.addEventListener('livewire:initialized', function() {
        
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            events: @json($assessments),
            initialView: 'dayGridMonth',
            displayEventTime: false,
            eventDisplay: 'block',
            
        });
        calendar.render();
    });

    </script>
@endpush

