
<div>
    <flux:heading size="xl" class="mb-4">Import Courses</flux:heading>
    <div class="space-y-6">
        <form action="{{ route('import.courses.upload') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <label class="label mb-4">Spreadsheet</label>
            <div class="box mb-4 overflow-x-auto">
                <flux:text>Format (All fields are required):</flux:text>
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>Course Title | Code | Discipline | Active (Yes/No)</pre>
                </div>
                <br>
                <flux:text>For example:</flux:text>
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>Aero Engineering | ENG4037 | Aero | Yes</pre>
                </div>
                <flux:spacer />
            </div>
            <div>
                <flux:input type="file" name="importFile" accept=".xlsx,.xls" wire:model="importFile" class="mb-4"/>
                <flux:button type="submit" variant="primary">Upload</flux:button>
            </div>
        </form>
    </div>
</div>
