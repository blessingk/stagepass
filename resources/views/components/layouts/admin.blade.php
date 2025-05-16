<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="h-full">
    <div class="flex h-full bg-gray-100">
        <!-- Sidebar -->
        <aside class="flex flex-col w-64 min-h-screen px-4 py-8 bg-gray-800 border-r rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700">
            <a href="{{ route('home') }}" class="mx-auto">
                <span class="text-2xl font-bold text-white">{{ config('app.name') }}</span>
            </a>

            <div class="flex flex-col justify-between flex-1 mt-6">
                <nav>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }} flex items-center px-4 py-2 rounded-lg">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>

                        <span class="mx-4 font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }} flex items-center px-4 py-2 mt-5 rounded-lg">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <span class="mx-4 font-medium">Events</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }} flex items-center px-4 py-2 mt-5 rounded-lg">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>

                        <span class="mx-4 font-medium">Users</span>
                    </a>

                    <a href="{{ route('admin.purchases.index') }}" class="{{ request()->routeIs('admin.purchases.*') ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }} flex items-center px-4 py-2 mt-5 rounded-lg">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>

                        <span class="mx-4 font-medium">Purchases</span>
                    </a>
                </nav>

                <div class="mt-6">
                    <div class="flex items-center justify-between mt-6">
                        <a href="#" class="flex items-center gap-x-2">
                            <img class="object-cover rounded-full h-7 w-7" src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}" alt="avatar">
                            <span class="text-sm font-medium text-gray-200">{{ auth()->user()->name }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-h-screen">
            <main class="p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
