<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SalesRecord;
use App\Models\Setting;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->load('locations');
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);
        $activeLocation = Location::find($activeLocationId);

        $isGlobalView = $user->isSuperAdmin() || $user->isOwner();

        $expiryDays = (int) Setting::getValue('expiry_warning_days', '30');

        // 1. Total Active Products
        $totalProducts = Product::active()->count();

        // 2. Total Stock Quantity
        $batchQuery = ProductBatch::query();
        if (!$isGlobalView && $activeLocationId) {
            $batchQuery->where('location_id', $activeLocationId);
        }
        $totalStockItems = (float) $batchQuery->sum('remaining_qty');

        // 3. Low stock warning count
        $lowStockCount = DB::table('product_stock_settings as pss')
            ->join('products as p', 'p.id', '=', 'pss.product_id')
            ->leftJoin('product_batches as pb', function ($join) {
                $join->on('pb.product_id', '=', 'pss.product_id')
                     ->on('pb.location_id', '=', 'pss.location_id');
            })
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('pss.location_id', $activeLocationId))
            ->where('p.is_active', true)
            ->whereNull('p.deleted_at')
            ->select('pss.product_id', 'pss.location_id', 'pss.min_stock', DB::raw('COALESCE(SUM(pb.remaining_qty), 0) as total_qty'))
            ->groupBy('pss.product_id', 'pss.location_id', 'pss.min_stock')
            ->havingRaw('COALESCE(SUM(pb.remaining_qty), 0) <= pss.min_stock')
            ->get()
            ->count();

        // 4. Expiring soon count
        $expiringSoonCount = ProductBatch::available()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($expiryDays))
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->count();

        // 5. Expired count
        $expiredCount = ProductBatch::available()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now())
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->count();

        // 6. Recent Sales (Today / This Month)
        $salesToday = SalesRecord::query()
            ->whereDate('sold_at', today())
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->sum('total_amount');

        $salesThisMonth = SalesRecord::query()
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->sum('total_amount');

        // 7. Operational Workflow Action Items (Pending Approvals / In-Transit)
        $pendingReceipts = GoodsReceipt::where('status', 'draft')
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->count();

        $pendingTransfers = StockTransfer::where('status', 'submitted')
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where(fn($query) => $query->where('from_location_id', $activeLocationId)->orWhere('to_location_id', $activeLocationId)))
            ->count();

        $inTransitTransfers = StockTransfer::where('status', 'shipped')
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where(fn($query) => $query->where('from_location_id', $activeLocationId)->orWhere('to_location_id', $activeLocationId)))
            ->count();

        // 8. Top 5 Selling Products this month
        $topProducts = DB::table('sales_record_items as sri')
            ->join('sales_records as sr', 'sr.id', '=', 'sri.sales_record_id')
            ->join('products as p', 'p.id', '=', 'sri.product_id')
            ->join('units as u', 'u.id', '=', 'p.base_unit_id')
            ->whereNull('sr.deleted_at')
            ->whereMonth('sr.sold_at', now()->month)
            ->whereYear('sr.sold_at', now()->year)
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('sr.location_id', $activeLocationId))
            ->select('p.name', 'u.symbol as unit', DB::raw('SUM(sri.qty) as total_qty'), DB::raw('SUM(sri.subtotal) as total_revenue'))
            ->groupBy('p.id', 'p.name', 'u.symbol')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 9. Sales trend last 7 days for Chart
        $salesTrend = DB::table('sales_records')
            ->whereNull('deleted_at')
            ->where('sold_at', '>=', now()->subDays(6)->startOfDay())
            ->when(!$isGlobalView && $activeLocationId, fn($q) => $q->where('location_id', $activeLocationId))
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return Inertia::render('Dashboard/Index', [
            'activeLocation' => $activeLocation,
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalStockItems' => $totalStockItems,
                'lowStockCount' => $lowStockCount,
                'expiringSoonCount' => $expiringSoonCount,
                'expiredCount' => $expiredCount,
                'salesToday' => (float) $salesToday,
                'salesThisMonth' => (float) $salesThisMonth,
                'pendingReceipts' => $pendingReceipts,
                'pendingTransfers' => $pendingTransfers,
                'inTransitTransfers' => $inTransitTransfers,
            ],
            'topProducts' => $topProducts,
            'salesTrend' => $salesTrend,
        ]);
    }
}
