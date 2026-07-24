<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ReturnDocument;
use App\Models\SalesRecord;
use App\Models\Setting;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    // 1. Stock & Valuation Report
    public function stock(Request $request): Response
    {
        $locationId = $request->input('location_id');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $targetLocationId = $locationId ?: ($user->isSuperAdmin() || $user->isOwner() ? null : $activeLocationId);

        $products = Product::with(['category', 'baseUnit', 'prices'])
            ->select('products.*')
            ->active()
            ->orderBy('name')
            ->paginate(20)
            ->through(function ($p) use ($targetLocationId) {
                $batches = ProductBatch::where('product_id', $p->id)
                    ->when($targetLocationId, fn($q) => $q->where('location_id', $targetLocationId))
                    ->get();

                $totalQty = $batches->sum('remaining_qty');
                $purchasePrice = $p->prices->where('price_type', 'purchase')->first()?->price ?? 0;
                $retailPrice = $p->prices->where('price_type', 'retail')->first()?->price ?? 0;

                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'category' => $p->category?->name,
                    'base_unit' => $p->baseUnit?->symbol,
                    'total_qty' => (float) $totalQty,
                    'purchase_price' => (float) $purchasePrice,
                    'retail_price' => (float) $retailPrice,
                    'total_valuation_hpp' => (float) ($totalQty * $purchasePrice),
                    'total_valuation_retail' => (float) ($totalQty * $retailPrice),
                ];
            });

        return Inertia::render('Reports/StockReport', [
            'products' => $products,
            'locations' => Location::active()->get(),
            'filters' => ['location_id' => $targetLocationId],
        ]);
    }

    // 2. Ledger Movement Report
    public function ledger(Request $request): Response
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $locationId = $request->input('location_id');

        $movements = StockLedger::with(['product.baseUnit', 'location'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return Inertia::render('Reports/LedgerReport', [
            'movements' => $movements,
            'locations' => Location::active()->get(),
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate, 'location_id' => $locationId],
        ]);
    }

    // 3. Expiry Warning Report
    public function expiry(Request $request): Response
    {
        $days = (int) $request->input('days', Setting::getValue('expiry_warning_days', '30'));
        $locationId = $request->input('location_id');

        $expiringBatches = ProductBatch::with(['product.baseUnit', 'location'])
            ->available()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->orderBy('expiry_date', 'asc')
            ->paginate(20);

        return Inertia::render('Reports/ExpiryReport', [
            'batches' => $expiringBatches,
            'locations' => Location::active()->get(),
            'filters' => ['days' => $days, 'location_id' => $locationId],
        ]);
    }

    // 4. Sales Report
    public function sales(Request $request): Response
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $locationId = $request->input('location_id');

        $sales = SalesRecord::with(['location', 'creator'])
            ->whereBetween(DB::raw('DATE(sold_at)'), [$startDate, $endDate])
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->orderBy('sold_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_sales_count' => SalesRecord::whereBetween(DB::raw('DATE(sold_at)'), [$startDate, $endDate])->when($locationId, fn($q) => $q->where('location_id', $locationId))->count(),
            'total_revenue' => (float) SalesRecord::whereBetween(DB::raw('DATE(sold_at)'), [$startDate, $endDate])->when($locationId, fn($q) => $q->where('location_id', $locationId))->sum('total_amount'),
        ];

        return Inertia::render('Reports/SalesReport', [
            'sales' => $sales,
            'summary' => $summary,
            'locations' => Location::active()->get(),
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate, 'location_id' => $locationId],
        ]);
    }

    // 5. Goods Receipt Report
    public function goodsReceipts(Request $request): Response
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $receipts = GoodsReceipt::with(['supplier', 'location'])
            ->where('status', 'received')
            ->whereBetween(DB::raw('DATE(received_at)'), [$startDate, $endDate])
            ->orderBy('received_at', 'desc')
            ->paginate(20);

        return Inertia::render('Reports/GoodsReceiptReport', [
            'receipts' => $receipts,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    // 6. Transfer Report
    public function transfers(Request $request): Response
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $transfers = StockTransfer::with(['fromLocation', 'toLocation'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Reports/TransferReport', [
            'transfers' => $transfers,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    // 7. Returns Report
    public function returns(Request $request): Response
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $returns = ReturnDocument::with(['location', 'supplier', 'relatedLocation'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Reports/ReturnReport', [
            'returns' => $returns,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    // 8. Fast & Slow Moving Report
    public function moving(Request $request): Response
    {
        $months = (int) $request->input('months', 3);

        $movingData = DB::table('products as p')
            ->join('units as u', 'u.id', '=', 'p.base_unit_id')
            ->leftJoin('sales_record_items as sri', 'sri.product_id', '=', 'p.id')
            ->leftJoin('sales_records as sr', function ($join) use ($months) {
                $join->on('sr.id', '=', 'sri.sales_record_id')
                     ->whereNull('sr.deleted_at')
                     ->where('sr.sold_at', '>=', now()->subMonths($months));
            })
            ->whereNull('p.deleted_at')
            ->where('p.is_active', true)
            ->select('p.id', 'p.sku', 'p.name', 'u.symbol as unit', DB::raw('COALESCE(SUM(sri.qty), 0) as total_sold'))
            ->groupBy('p.id', 'p.sku', 'p.name', 'u.symbol')
            ->orderByDesc('total_sold')
            ->get();

        return Inertia::render('Reports/MovingReport', [
            'items' => $movingData,
            'filters' => ['months' => $months],
        ]);
    }
}
