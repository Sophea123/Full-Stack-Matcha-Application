@php
    $currentImage = $product->previewImageUrl();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-5 lg:col-span-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <h2 class="font-bold text-stone-900">Product details</h2>
            <p class="text-xs text-stone-500">These fields appear in the customer app.</p>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="title" class="mb-1.5 block text-sm font-semibold text-stone-700">Name <span class="text-red-600">*</span></label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $product->title) }}"
                        required
                        maxlength="255"
                        placeholder="Mango Matcha"
                        class="w-full rounded-xl border {{ $errors->has('title') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                    >
                    @error('title')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-sm font-semibold text-stone-700">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Fresh mango blended with matcha and milk."
                        class="w-full rounded-xl border {{ $errors->has('description') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                    >{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="price" class="mb-1.5 block text-sm font-semibold text-stone-700">Price (USD) <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-sm font-semibold text-stone-500">$</span>
                            <input
                                id="price"
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('price', $product->price) }}"
                                required
                                placeholder="5.25"
                                class="w-full rounded-xl border {{ $errors->has('price') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 py-2.5 pl-9 pr-3.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                            >
                        </div>
                        @error('price')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="mb-1.5 block text-sm font-semibold text-stone-700">Category <span class="text-red-600">*</span></label>
                        <input
                            id="category"
                            name="category"
                            type="text"
                            list="category-options"
                            value="{{ old('category', $product->category) }}"
                            required
                            maxlength="100"
                            placeholder="Matcha"
                            class="w-full rounded-xl border {{ $errors->has('category') ? 'border-red-400' : 'border-stone-300' }} bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                        >
                        <datalist id="category-options">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}"></option>
                            @endforeach
                        </datalist>
                        @error('category')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <h2 class="font-bold text-stone-900">Product image</h2>
            <p class="text-xs text-stone-500">Upload a new photo, or keep the current image / use a filename or URL.</p>

            <div class="mt-5 flex flex-col gap-5 sm:flex-row">
                <div class="shrink-0">
                    @if ($currentImage)
                        <img src="{{ $currentImage }}" alt="Current product image" class="h-28 w-28 rounded-2xl object-cover ring-1 ring-stone-200">
                    @else
                        <div class="grid h-28 w-28 place-items-center rounded-2xl bg-stone-100 text-3xl">🍵</div>
                    @endif
                </div>

                <div class="min-w-0 flex-1 space-y-3">
                    <div>
                        <label for="image" class="mb-1.5 block text-sm font-semibold text-stone-700">Upload image</label>
                        <input
                            id="image"
                            name="image"
                            type="file"
                            accept="image/*"
                            class="block w-full cursor-pointer rounded-xl border border-stone-300 bg-stone-50 text-sm text-stone-600 file:mr-3 file:cursor-pointer file:border-0 file:bg-stone-200 file:px-3 file:py-2.5 file:text-sm file:font-semibold file:text-stone-700 hover:file:bg-stone-300"
                        >
                        @error('image')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="image_url" class="mb-1.5 block text-sm font-semibold text-stone-700">Or image filename / URL</label>
                        <input
                            id="image_url"
                            name="image_url"
                            type="text"
                            value="{{ old('image_url', $product->image_url) }}"
                            maxlength="2048"
                            placeholder="regular_matcha.png"
                            class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20"
                        >
                        @error('image_url')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="lg:col-span-1">
        <div class="space-y-4 lg:sticky lg:top-6">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                <h2 class="font-bold text-stone-900">Publish</h2>
                <p class="mt-1 text-xs text-stone-500">Changes are visible to the mobile app as soon as you save.</p>

                <button
                    type="submit"
                    class="mt-4 w-full rounded-xl bg-[#064e2b] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#043b21] focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2"
                >
                    {{ $submitLabel }}
                </button>

                <a href="{{ route('admin.products.index') }}" class="mt-2 block w-full rounded-xl border border-stone-300 px-4 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Cancel</a>
            </section>

            @if ($product->exists)
                <section class="rounded-2xl border border-red-200 bg-red-50 p-5">
                    <h2 class="font-bold text-red-900">Delete this product</h2>
                    <p class="mt-1 text-xs text-red-700">This removes it from the customer menu permanently.</p>
                </section>
            @endif
        </div>
    </aside>
</form>

@if ($product->exists)
    <form
        method="POST"
        action="{{ route('admin.products.destroy', $product) }}"
        onsubmit="return confirm('Delete {{ addslashes($product->title) }}? This cannot be undone.')"
        class="mt-4 lg:max-w-xs"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-50">Delete product</button>
    </form>
@endif
