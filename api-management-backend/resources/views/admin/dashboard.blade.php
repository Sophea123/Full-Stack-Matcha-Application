@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">Products</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-900">{{ $productCount }}</p>
            <p class="mt-1 text-xs text-stone-400">Items in the live menu</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">Categories</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-900">{{ $categoryCount }}</p>
            <p class="mt-1 text-xs text-stone-400">Used by the app filters</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <p class="text-sm font-medium text-stone-500">Average price</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-900">${{ number_format($averagePrice, 2) }}</p>
            <p class="mt-1 text-xs text-stone-400">Across all products</p>
        </div>
        <div class="rounded-2xl bg-[#064e2b] p-5 text-white shadow-sm">
            <p class="text-sm font-medium text-white/70">Quick action</p>
            <a
                href="{{ route('admin.products.create') }}"
                class="mt-3 inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-sm font-bold text-[#064e2b] transition hover:bg-emerald-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                </svg>
                Add a product
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="xl:col-span-2">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
                <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                    <div>
                        <h2 class="font-bold text-stone-900">Recently added</h2>
                        <p class="text-xs text-stone-500">The five newest menu items.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View all</a>
                </div>

                @if ($recentProducts->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-stone-500">No products yet. Add the first one.</p>
                @else
                    <ul class="divide-y divide-stone-100">
                        @foreach ($recentProducts as $product)
                            <li class="flex items-center gap-4 px-5 py-3">
                                @if ($product->previewImageUrl())
                                    <img src="{{ $product->previewImageUrl() }}" alt="" class="h-12 w-12 rounded-xl object-cover ring-1 ring-stone-200">
                                @else
                                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-stone-100 text-lg">🍵</span>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-stone-900">{{ $product->title }}</p>
                                    <p class="text-xs text-stone-500">{{ $product->category }} · updated {{ $product->updated_at?->diffForHumans() }}</p>
                                </div>

                                <span class="font-bold text-stone-900">${{ number_format((float) $product->price, 2) }}</span>
                                <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-stone-300 px-2.5 py-1 text-xs font-semibold text-stone-700 hover:bg-stone-50">Edit</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <h2 class="font-bold text-stone-900">Catalog by category</h2>
            <p class="text-xs text-stone-500">How the menu is distributed.</p>

            @if ($categories->isEmpty())
                <p class="mt-6 text-sm text-stone-500">No categories yet.</p>
            @else
                <ul class="mt-5 space-y-4">
                    @foreach ($categories as $row)
                        @php
                            $percentage = $productCount > 0 ? round(($row->total / $productCount) * 100) : 0;
                        @endphp
                        <li>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-stone-700">{{ $row->category }}</span>
                                <span class="text-stone-500">{{ $row->total }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-stone-100">
                                <div class="h-full rounded-full bg-emerald-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
