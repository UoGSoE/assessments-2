<div>
    <div class="flex items-baseline gap-4 justify-start">
        <flux:heading size="xl" class="mb-4 flex-1">Your Assessments</flux:heading>
        
    </div>
    <flux:select class="mb-4" wire:model.live="yearFilter">
            <flux:select.option value="all">All years</flux:select.option>
            <flux:select.option value="1">1st</flux:select.option>
            <flux:select.option value="2">2nd</flux:select.option>
            <flux:select.option value="3">3rd</flux:select.option>
            <flux:select.option value="4">4th</flux:select.option>
            <flux:select.option value="5">5th</flux:select.option>
    </flux:select>
    <div id='calendar'></div>
</div>

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
    <script>

    document.addEventListener('livewire:initialized', function() {
        
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            events: @json($assessments),
            initialView: 'dayGridMonth',
            // TODO: Would this actually help?
            //initialDate: @json($initialDate ?? now()->format('Y-m-d')),
            displayEventTime: false,
            eventDisplay: 'block',
            
        });
        calendar.render();
    });

    </script>
@endpush
