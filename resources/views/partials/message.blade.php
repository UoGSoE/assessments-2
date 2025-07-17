@if (session()->has('message'))
    <div class="mt-4">
        <b>{{ session('message') }}</b>
    </div>
@endif