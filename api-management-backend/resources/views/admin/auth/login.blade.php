<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin sign in · Brewly</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="grid min-h-screen place-items-center bg-[#f3ead8] p-4 font-sans text-stone-900 antialiased">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-[#064e2b] text-2xl shadow-lg">🍵</div>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-[#064e2b]">Brewly Admin</h1>
                <p class="mt-1 text-sm text-stone-600">Sign in to manage the menu.</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-xl shadow-stone-300/40 sm:p-8">
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-stone-700">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                            placeholder="you@example.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-stone-700">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                            placeholder="••••••••"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-700">
                        Keep me signed in
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[#064e2b] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#043b21] focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2"
                    >
                        Sign in
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-stone-500">
                Administrator access only. Customers use the Brewly mobile app.
            </p>
        </div>
    </body>
</html>
