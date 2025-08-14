<div>
    <flux:heading>Statistics</flux:heading>
    <flux:chart wire:model="data" class="aspect-3/1">
        <flux:chart.viewport class="min-h-[20rem]">
            <flux:chart.svg>
                <flux:chart.line field="studentLogins" class="text-pink-500 dark:text-pink-400" />
                <flux:chart.line field="staffLogins" class="text-blue-500 dark:text-blue-400" />

                <flux:chart.axis axis="x" field="month">
                    <flux:chart.axis.line />
                    <flux:chart.axis.tick />
                </flux:chart.axis>

                <flux:chart.axis axis="y">
                    <flux:chart.axis.grid />
                    <flux:chart.axis.tick />
                </flux:chart.axis>

                <flux:chart.cursor />
            </flux:chart.svg>

            <flux:chart.tooltip>
                <flux:chart.tooltip.heading field="month" />
                <flux:chart.tooltip.value field="studentLogins" label="Student Logins" />
            </flux:chart.tooltip>
            <flux:chart.tooltip>
                <flux:chart.tooltip.heading field="month" />
                <flux:chart.tooltip.value field="staffLogins" label="Staff Logins" />
            </flux:chart.tooltip>
        </flux:chart.viewport>
        <div>
            <flux:chart.legend label="Student Logins">
                <flux:chart.legend.indicator class="bg-pink-400" />
            </flux:chart.legend>
            <flux:chart.legend label="Staff Logins">
                <flux:chart.legend.indicator class="bg-blue-400" />
            </flux:chart.legend>
        </div>
    </flux:chart>
</div>
