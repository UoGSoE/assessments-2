<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>{{ config('app.name') }}</title>
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:brand href="#" name="Assessment Calendar" class="px-2 dark:hidden" />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" href="{{ route('home') }}" :current="request()->routeIs('home')">Home</flux:navlist.item>
            @if(auth()->check() && auth()->user()->is_admin)
            <flux:navlist.item icon="user-group" href="{{ route('assessment.index') }}" :current="request()->routeIs('assessment.index')">Admin</flux:navlist.item>
            @endif
        </flux:navlist>

        <flux:spacer />
        @auth
        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:profile  name="{{ auth()->check() ? auth()->user()->name : 'Guest' }}" />

            <flux:menu>
                <flux:menu.item>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button icon="arrow-right-start-on-rectangle" type="submit" class="text-left">Logout</button>
                    </form>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @endauth
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />
        @auth
        <flux:dropdown position="top" align="start">
            <flux:profile name="{{ auth()->check() ? auth()->user()->name : 'Guest' }}" />

            <flux:menu>
                <flux:menu.item>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full" icon="arrow-right-start-on-rectangle">Logout</button>
                    </form>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @endauth
    </flux:header>

    <flux:main>
        {{$slot}}
    </flux:main>

    @fluxScripts
    @stack('scripts')
    @persist('toast')
        <flux:toast />
    @endpersist
</body>
</html>
