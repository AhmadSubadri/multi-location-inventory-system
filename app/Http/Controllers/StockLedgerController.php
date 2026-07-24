<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockLedgerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $locationId = $request->input('location_id');
        $productId = $request->input('product_id');
        $type = $request->input('type');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $ledgers = StockLedger::with(['product.baseUnit', 'batch', 'location', 'creator'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where('location_id', $activeLocationId);
            })
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('StockLedgers/Index', [
            'ledgers' => $ledgers,
            'locations' => Location::active()->orderBy('name')->get(),
            'products' => Product::active()->orderBy('name')->get(),
            'filters' => [
                'search' => $search,
                'location_id' => $locationId,
                'product_id' => $productId,
                'type' => $type,
            ],
        ]);
    }
}
