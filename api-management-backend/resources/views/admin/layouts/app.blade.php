<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') · Brewly Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 font-sans text-stone-900 antialiased">
        <div class="min-h-screen lg:flex">
            <aside class="hidden w-64 shrink-0 flex-col bg-[#064e2b] p-5 text-white lg:flex">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15 text-xl">🍵</span>
                    <span>
                        <span class="block text-lg font-bold leading-tight">Brewly</span>
                        <span class="block text-xs text-white/60">Admin dashboard</span>
                    </span>
                </a>

                <nav class="mt-8 space-y-1 text-sm font-medium">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-[#064e2b]' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z" />
                        </svg>
                        Dashboard
                    </a>
                    <a
                        href="{{ route('admin.products.index') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ request()->routeIs('admin.products.*') ? 'bg-white text-[#064e2b]' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Products
                    </a>
                    <a
                        href="{{ route('admin.products.create') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white/80 transition hover:bg-white/10 hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                        </svg>
                        Add product
                    </a>
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-[#064e2b]' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1m7-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 9v-1a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11" />
                        </svg>
                        Users &amp; admins
                    </a>
                </nav>

                <div class="mt-auto rounded-xl bg-white/10 p-3 text-xs text-white/70">
                    Signed in as
                    <span class="mt-0.5 block truncate font-semibold text-white">{{ auth()->user()->email }}</span>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between gap-4 border-b border-stone-200 bg-white px-4 py-3 sm:px-6 lg:px-8">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Brewly admin</p>
                        <h1 class="truncate text-xl font-bold text-stone-900">@yield('heading', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm text-stone-500 sm:block">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 transition hover:border-stone-400 hover:bg-stone-50"
                            >
                                Sign out
                            </button>
                        </form>
                    </div>
                </header>

                <nav class="flex gap-2 overflow-x-auto border-b border-stone-200 bg-white px-4 py-2 lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-800 text-white' : 'text-stone-600' }}">Dashboard</a>
                    <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ request()->routeIs('admin.products.*') ? 'bg-emerald-800 text-white' : 'text-stone-600' }}">Products</a>
                    <a href="{{ route('admin.products.create') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-stone-600">Add product</a>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-emerald-800 text-white' : 'text-stone-600' }}">Users</a>
                </nav>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    @if (session('status'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                            <p class="font-semibold">Please fix the following:</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
