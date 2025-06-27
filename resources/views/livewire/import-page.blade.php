
<div>
    <flux:heading size="xl" class="mb-4">Import {{ $title }}</flux:heading>
    <flux:text class="mb-4 {{ $fileType === 'student-courses' || $fileType === 'staff-courses' ? 'text-red-500' : '' }}">{{ $description }}</flux:text>
    <div class="space-y-6">
        <form wire:submit="chooseImport" class="space-y-4">
            <label class="label mb-4">Spreadsheet</label>
            <div class="box mb-4">
                <flux:text>Format (All fields are required):</flux:text>
                <pre class="bg-gray-100 p-4">{{ $formatText }}</pre>
                <br>
                <flux:text>For example:</flux:text>
                <pre class="bg-gray-100 p-4">{{ $exampleText }}</pre>
                <flux:spacer />
                
            </div>
            <div>
                <flux:input type="file" accept=".xlsx,.xls" wire:model="importFile" class="mb-4"/>
                <flux:button type="submit" variant="primary">Upload</flux:button>
                @error('importFile') <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text> @enderror
            </div>
        </form>
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
