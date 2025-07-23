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

<div
    x-data="{
        calendar: null,
        events: $wire.entangle('assessments'),
        
        initCalendar() {
            this.calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                events: this.events,
                // your other calendar options here
            });
            
            this.calendar.render();
            
            // Watch for changes to assessments and refresh calendar
            this.$watch('events', () => {
                this.calendar.destroy();
                this.initCalendar();
            });
        },
        init() {
            this.initCalendar();
        }
    }"
>
</div>
</div>

@assets
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
@endassets


