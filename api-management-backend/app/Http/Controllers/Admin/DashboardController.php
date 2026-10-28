<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show an overview of the catalog.
     */
    public function __invoke(): View
    {
        $products = Product::query();

        return view('admin.dashboard', [
            'productCount' => (clone $products)->count(),
            'categoryCount' => (clone $products)->distinct()->count('category'),
            'averagePrice' => (float) (clone $products)->avg('price'),
            'recentProducts' => (clone $products)->latest('id')->limit(5)->get(),
            'categories' => (clone $products)
                ->selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get(),
        ]);
    }
}
