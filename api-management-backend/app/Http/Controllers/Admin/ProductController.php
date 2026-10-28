<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * List and search the catalog.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $category = trim((string) $request->query('category'));
        $perPage = $this->perPage($request);

        $products = Product::query()
            ->when($search !== '', fn ($query) => $query->whereRaw(
                'lower(title) like ?',
                ['%'.Str::lower($search).'%'],
            ))
            ->when($category !== '', fn ($query) => $query->whereRaw(
                'lower(category) = ?',
                [Str::lower($category)],
            ))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Product::query()
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'search' => $search,
            'category' => $category,
            'perPage' => $perPage,
            'perPageOptions' => [5, 10, 25, 50],
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product,
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): RedirectResponse
    {
        $product = Product::query()->create($this->validated($request));

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$product->title} was added to the menu.");
    }

    /**
     * Show the edit form.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request));

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$product->title} was updated.");
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $title = $product->title;
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', "{$title} was deleted.");
    }

    /**
     * Validate the shared create/update form.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        unset($data['image']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug($data['title']).'-'.Str::lower(Str::random(6)).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $data['image_url'] = $filename;
        }

        return $data;
    }

    /**
     * Resolve a valid page size from the query string.
     */
    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 5);

        return in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 5;
    }

    /**
     * Existing categories plus the defaults, alphabetised.
     *
     * @return list<string>
     */
    private function categories(): array
    {
        $categories = Product::query()
            ->distinct()
            ->pluck('category')
            ->all();

        $categories = array_values(array_unique(array_merge(
            ['Matcha', 'Coffee', 'Dessert'],
            $categories,
        )));

        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return $categories;
    }
}
