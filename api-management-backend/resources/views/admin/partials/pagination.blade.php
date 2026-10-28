@php
    $elements = $paginator->linkCollection();
    $label = \Illuminate\Support\Str::plural($label ?? 'item', $paginator->total());
@endphp

@if ($paginator->total() > 0)
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-stone-500">
            Showing
            <span class="font-semibold text-stone-800">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-semibold text-stone-800">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-semibold text-stone-800">{{ $paginator->total() }}</span>
            {{ $label }}
        </p>

        @if ($paginator->hasPages())
            <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex cursor-not-allowed items-center rounded-lg border border-stone-200 px-3 py-1.5 text-sm font-semibold text-stone-300">
                        Prev
                    </span>
                @else
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                    >
                        Prev
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex min-w-9 items-center justify-center px-1 text-sm text-stone-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page === $paginator->currentPage())
                                <span aria-current="page" class="inline-flex min-w-9 items-center justify-center rounded-lg border border-emerald-800 bg-emerald-800 px-2.5 py-1.5 text-sm font-bold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a
                                    href="{{ $url }}"
                                    class="inline-flex min-w-9 items-center justify-center rounded-lg border border-stone-300 px-2.5 py-1.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                    >
                        Next
                    </a>
                @else
                    <span class="inline-flex cursor-not-allowed items-center rounded-lg border border-stone-200 px-3 py-1.5 text-sm font-semibold text-stone-300">
                        Next
                    </span>
                @endif
            </nav>
        @else
            <p class="text-xs font-medium text-stone-400">Page 1 of 1</p>
        @endif
    </div>
@endif
