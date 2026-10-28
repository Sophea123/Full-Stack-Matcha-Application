@php
    $isEditing = $user->exists;
    $isChecked = (bool) old('is_admin', $user->is_admin ?? false);
    $initials = collect(explode(' ', $user->name ?? ''))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<form method="POST" action="{{ $action }}" class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-5 lg:col-span-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <div class="flex items-center gap-3">
                <span class="grid h-12 w-12 place-items-center rounded-full text-base font-bold {{ $user->is_admin ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                    {{ $initials !== '' ? $initials : '?' }}
                </span>
                <div>
                    <h2 class="font-bold text-stone-900">Account details</h2>
                    <p class="text-xs text-stone-500">
                        {{ $isEditing ? 'Change the name, email, or password for this account.' : 'Create an account that can sign in to the mobile app or this dashboard.' }}
                    </p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-stone-700">Name <span class="text-red-600">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        autocomplete="name"
                        placeholder="Sophea Chan"
                        class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                    >
                    @error('name')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-stone-700">Email <span class="text-red-600">*</span></label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full rounded-xl border {{ $errors->has('email') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                    >
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-stone-700">
                            {{ $isEditing ? 'New password' : 'Password' }}
                            @if (! $isEditing)
                                <span class="text-red-600">*</span>
                            @endif
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'At least 8 characters' }}"
                            @if (! $isEditing) required @endif
                            class="w-full rounded-xl border {{ $errors->has('password') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                        >
                        @if ($isEditing)
                            <p class="mt-1 text-xs text-stone-400">Only fill this in to reset the password.</p>
                        @endif
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-stone-700">Confirm password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Repeat the password"
                            @if (! $isEditing) required @endif
                            class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                        >
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <h2 class="font-bold text-stone-900">Permissions</h2>
            <p class="text-xs text-stone-500">Administrators can manage products and other accounts.</p>

            <div class="mt-4 flex items-start gap-3 rounded-xl border {{ $isChecked ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-stone-50' }} p-4">
                @if ($isSelf)
                    <input type="hidden" name="is_admin" value="1">
                @endif
                <input
                    id="is_admin"
                    name="{{ $isSelf ? 'is_admin_preview' : 'is_admin' }}"
                    type="checkbox"
                    value="1"
                    @checked($isChecked)
                    @disabled($isSelf)
                    class="mt-0.5 h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-700 disabled:opacity-60"
                >
                <div class="min-w-0">
                    <label for="is_admin" class="block text-sm font-semibold text-stone-800">Administrator access</label>
                    <p class="mt-0.5 text-xs text-stone-500">
                        @if ($isSelf)
                            You cannot remove your own administrator access. Another admin must do it.
                        @else
                            Grants access to this dashboard. Leave unchecked for a normal customer account.
                        @endif
                    </p>
                    @error('is_admin')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
    </div>

    <aside class="lg:col-span-1">
        <div class="space-y-4 lg:sticky lg:top-6">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                <h2 class="font-bold text-stone-900">{{ $isEditing ? 'Save changes' : 'Create account' }}</h2>
                <p class="mt-1 text-xs text-stone-500">
                    {{ $isEditing ? 'The account keeps the same history and API access.' : 'The password can be changed by the account owner later.' }}
                </p>

                <button
                    type="submit"
                    class="mt-4 w-full rounded-xl bg-[#064e2b] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#043b21] focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2"
                >
                    {{ $submitLabel }}
                </button>

                <a href="{{ route('admin.users.index') }}" class="mt-2 block w-full rounded-xl border border-stone-300 px-4 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Cancel</a>
            </section>

            @if ($user->exists && ! $isSelf)
                <section class="rounded-2xl border border-red-200 bg-red-50 p-5">
                    <h2 class="font-bold text-red-900">Delete this account</h2>
                    <p class="mt-1 text-xs text-red-700">The account and its API tokens are removed permanently.</p>
                </section>
            @endif
        </div>
    </aside>
</form>

@if ($user->exists && ! $isSelf)
    <form
        method="POST"
        action="{{ route('admin.users.destroy', $user) }}"
        onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');"
        class="mt-4 lg:max-w-xs"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-50">Delete account</button>
    </form>
@endif
