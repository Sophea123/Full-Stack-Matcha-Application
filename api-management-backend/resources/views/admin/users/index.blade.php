@extends('admin.layouts.app')

@section('title', 'Users & admins')
@section('heading', 'Users & admins')

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">All accounts</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-900">{{ $totalUsers }}</p>
            <p class="mt-1 text-xs text-stone-400">Admins and customers</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">Administrators</p>
            <p class="mt-2 text-3xl font-extrabold text-emerald-800">{{ $adminCount }}</p>
            <p class="mt-1 text-xs text-stone-400">Can manage products and accounts</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">Customers</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-900">{{ $customerCount }}</p>
            <p class="mt-1 text-xs text-stone-400">Read-only access in the app</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
        <div class="flex flex-col gap-4 border-b border-stone-100 px-4 py-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-bold text-stone-900">Accounts</h2>
                <p class="text-xs text-stone-500">{{ $users->total() }} account(s) found.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search name or email..."
                        class="w-full rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-700 focus:bg-white sm:w-56"
                    >
                    <select name="role" class="rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-700 focus:bg-white">
                        <option value="">All roles</option>
                        <option value="admin" @selected($role === 'admin')>Administrators</option>
                        <option value="customer" @selected($role === 'customer')>Customers</option>
                    </select>
                    <button type="submit" class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Filter</button>
                    @if ($search !== '' || $role !== '')
                        <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 text-center text-sm font-semibold text-stone-500 hover:text-stone-800">Clear</a>
                    @endif
                </form>

                <a
                    href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#064e2b] px-3.5 py-2 text-sm font-bold text-white transition hover:bg-[#043b21]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    Add account
                </a>
            </div>
        </div>

        @if ($users->isEmpty())
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-stone-800">No accounts found</p>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $search !== '' || $role !== '' ? 'Try a different search or role.' : 'Add the first account to get started.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100 text-sm">
                    <thead class="bg-stone-50 text-left text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-5">Account</th>
                            <th class="px-4 py-3 font-semibold">Role</th>
                            <th class="hidden px-4 py-3 font-semibold md:table-cell">Joined</th>
                            <th class="px-4 py-3 text-right font-semibold sm:px-5">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($users as $account)
                            @php
                                $isSelf = $account->is(auth()->user());
                                $initials = collect(explode(' ', $account->name))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');
                            @endphp
                            <tr class="transition hover:bg-stone-50/70">
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-bold {{ $account->is_admin ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $initials !== '' ? $initials : '?' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-stone-900">
                                                {{ $account->name }}
                                                @if ($isSelf)
                                                    <span class="ml-1 rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-stone-500">You</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-xs text-stone-500">{{ $account->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($account->is_admin)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Administrator</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600">Customer</span>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-stone-500 md:table-cell">{{ $account->created_at?->format('M j, Y') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $account) }}" class="rounded-lg border border-stone-300 px-2.5 py-1.5 text-xs font-semibold text-stone-700 transition hover:bg-stone-100">Edit</a>
                                        @if (! $isSelf)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $account) }}"
                                                onsubmit="return confirm('Delete {{ addslashes($account->name) }}? Their API tokens will also be revoked.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-stone-100 px-4 py-3 sm:px-5">
                @include('admin.partials.pagination', ['paginator' => $users, 'label' => 'account'])
            </div>
        @endif
    </div>
@endsection
