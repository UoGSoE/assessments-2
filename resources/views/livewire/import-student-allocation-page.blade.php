<div>
    <flux:heading size="xl" class="mb-4">Import Student Course Allocations</flux:heading>

    <div class="space-y-6">
        <form action="{{ route('import.upload', ['type' => 'student-courses']) }}" method="post"
            enctype="multipart/form-data" class="space-y-4">
            @csrf
            <label class="label mb-4">Spreadsheet</label>
            <flux:text class="mb-4 text-red-500">Please ensure all courses are uploaded to the database first.
            </flux:text>
            <div class="box mb-4 overflow-x-auto">
                <flux:text>Format (All fields are required):</flux:text>
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>Forenames | Surname | GUID | Course Code</pre>
                </div>
                <br>
                <flux:text>For example:</flux:text>
                <div class="bg-gray-100 p-4 text-[14px] overflow-x-auto text-sm">
                    <pre>Jane | Smith | 123456789S | ENG1000</pre>
                </div>
                <flux:spacer />
            </div>
            <div>
                <flux:input type="file" name="importFile" accept=".xlsx,.xls" wire:model="importFile"
                    class="mb-4" />
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
