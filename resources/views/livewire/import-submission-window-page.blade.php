
<div>
    <flux:heading size="xl" class="mb-4">Import Submission Windows</flux:heading>
    <div class="space-y-6">
        <form action="{{ route('import.submission-windows.upload') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <flux:text>This is a tool to help you import submission windows from a spreadsheet.</flux:text>
            <flux:text>All columns are required, though "comments" can be left blank.</flux:text> 
            <flux:text><strong>Please note:</strong> this will only import submission windows, not deadlines.</flux:text>
            <flux:text>For importing deadlines, please use the <a class="text-blue-500" href="{{route('import.deadlines.show')}}"> Deadlines Import page.</a></flux:text>
            <flux:text>If the course code, staff email and assessment type is the same as an existing submission window, then the submission window date will be updated.</flux:text>
            <label class="label mb-4">Spreadsheet</label>
            <div class="box mb-4 overflow-x-auto">
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>course code | assessment type | feedback type | staff email | submission window from | submission window to | comments</pre>
                </div>
                <br>
                <flux:text>For example:</flux:text>
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>ENG4037 | Moodle Quiz | Moodle - Graded | Angela.Busse@glasgow.ac.uk | 26/06/2025 16:08 | 27/06/2025 16:08 | My moodle quiz is great</pre>
                </div>
                <flux:spacer />
            </div>
            <div>
                <flux:input type="file" name="importFile" accept=".xlsx,.xls" wire:model="importFile" class="mb-4"/>
                <flux:button type="submit" variant="primary">Upload</flux:button>
            </div>
        </form>
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
