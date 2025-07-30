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
    <div x-init="init()" x-data="{
        calendar: null,
        events: $wire.entangle('assessments'),
    
        initCalendar() {
    
            const calendarEl = document.getElementById('calendar');
            console.log('Calendar element:', calendarEl);
    
            this.calendar = new window.FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                plugins: [window.FullCalendar.dayGridPlugin],
                events: this.events,
                displayEventTime: false,
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
