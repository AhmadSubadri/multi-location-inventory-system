<?php

namespace App\Http\Controllers;

use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPriceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $type = $request->input('price_type');

        $prices = ProductPrice::with(['product.baseUnit', 'location'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($type, fn($q) => $q->where('price_type', $type))
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Prices/Index', [
            'prices' => $prices,
            'filters' => [
                'search' => $search,
                'price_type' => $type,
            ],
        ]);
    }
}
