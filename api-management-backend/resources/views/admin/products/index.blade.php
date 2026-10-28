@extends('admin.layouts.app')

@section('title', 'Products')
@section('heading', 'Products')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
        <div class="flex flex-col gap-4 border-b border-stone-100 px-4 py-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-bold text-stone-900">Menu items</h2>
                <p class="text-xs text-stone-500">{{ $products->total() }} product(s) in the catalog.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by name..."
                        class="w-full rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-700 focus:bg-white sm:w-52"
                    >
                    <select name="category" class="rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-700 focus:bg-white">
                        <option value="">All categories</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item }}" @selected(strcasecmp($category, $item) === 0)>{{ $item }}</option>
                        @endforeach
                    </select>
                    <select
                        name="per_page"
                        onchange="this.form.submit()"
                        aria-label="Products per page"
                        class="rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-700 focus:bg-white"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} per page</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Filter</button>
                    @if ($search !== '' || $category !== '')
                        <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-2 text-center text-sm font-semibold text-stone-500 hover:text-stone-800">Clear</a>
                    @endif
                </form>

                <a
                    href="{{ route('admin.products.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#064e2b] px-3.5 py-2 text-sm font-bold text-white transition hover:bg-[#043b21]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    Add product
                </a>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-stone-800">No products found</p>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $search !== '' || $category !== '' ? 'Try a different search or category.' : 'Add your first product to get started.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100 text-sm">
                    <thead class="bg-stone-50 text-left text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-5">Product</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold">Price</th>
                            <th class="hidden px-4 py-3 font-semibold md:table-cell">Updated</th>
                            <th class="px-4 py-3 text-right font-semibold sm:px-5">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($products as $product)
                            <tr class="transition hover:bg-stone-50/70">
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        @if ($product->previewImageUrl())
                                            <img src="{{ $product->previewImageUrl() }}" alt="" class="h-11 w-11 shrink-0 rounded-xl object-cover ring-1 ring-stone-200">
                                        @else
                                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-stone-100 text-base">🍵</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-stone-900">{{ $product->title }}</p>
                                            <p class="max-w-xs truncate text-xs text-stone-500">{{ $product->description ?: 'No description' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $product->category }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-stone-900">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-stone-500 md:table-cell">{{ $product->updated_at?->format('M j, Y') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-stone-300 px-2.5 py-1.5 text-xs font-semibold text-stone-700 transition hover:bg-stone-100">Edit</a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.products.destroy', $product) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($product->title) }}? This cannot be undone.')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-stone-100 px-4 py-3 sm:px-5">
                @include('admin.partials.pagination', ['paginator' => $products, 'label' => 'product'])
            </div>
        @endif
    </div>
@endsection
