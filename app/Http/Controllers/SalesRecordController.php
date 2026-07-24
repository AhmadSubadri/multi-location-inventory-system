<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discount;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SalesRecord;
use App\Models\SalesRecordItem;
use App\Models\Tax;
use App\Services\DocumentNumberService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesRecordController extends Controller
{
    public function __construct(
        protected DocumentNumberService $documentNumberService,
        protected StockLedgerService $stockLedgerService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $type = $request->input('record_type');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $sales = SalesRecord::with(['location', 'creator'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where('location_id', $activeLocationId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->when($type, fn($q) => $q->where('record_type', $type))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('SalesRecords/Index', [
            'sales' => $sales,
            'filters' => ['search' => $search, 'record_type' => $type],
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $products = Product::with(['baseUnit', 'prices', 'batches' => function ($b) use ($activeLocationId) {
            $b->where('location_id', $activeLocationId)->available()->fefo();
        }])
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('SalesRecords/Form', [
            'locations' => Location::active()->orderBy('name')->get(),
            'products' => $products,
            'taxes' => Tax::active()->get(),
            'discounts' => Discount::currentlyValid()->get(),
            'activeLocationId' => $activeLocationId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_contact' => ['nullable', 'string', 'max:50'],
            'record_type' => ['required', 'string', 'in:individual,daily_recap'],
            'sold_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.batch_id' => ['required', 'integer', 'exists:product_batches,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated) {
            $code = $this->documentNumberService->generate('sales_record');

            // Calculate totals
            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {
                $itemSubtotal = ($item['qty'] * $item['unit_price']);
                $subtotal += $itemSubtotal;
                $discountTotal += ($item['discount_amount'] ?? 0);
                $taxTotal += ($item['tax_amount'] ?? 0);
            }

            $totalAmount = $subtotal - $discountTotal + $taxTotal;

            $salesRecord = SalesRecord::create([
                'code' => $code,
                'location_id' => $validated['location_id'],
                'customer_name' => $validated['customer_name'] ?? 'Pelanggan Umum',
                'customer_contact' => $validated['customer_contact'] ?? null,
                'record_type' => $validated['record_type'],
                'sold_at' => $validated['sold_at'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountTotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $itemSubtotal = ($item['qty'] * $item['unit_price']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0);

                SalesRecordItem::create([
                    'sales_record_id' => $salesRecord->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'qty' => $item['qty'],
                    'unit_id' => $item['unit_id'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                ]);

                // Record stock ledger entry OUT_SALE immediately
                $this->stockLedgerService->recordEntry(
                    productId: $item['product_id'],
                    batchId: $item['batch_id'],
                    locationId: $validated['location_id'],
                    type: 'OUT_SALE',
                    qty: $item['qty'],
                    referenceType: SalesRecord::class,
                    referenceId: $salesRecord->id,
                    userId: auth()->id(),
                    notes: "Penjualan ({$validated['record_type']}) Dokumen {$code}"
                );
            }

            ActivityLog::log(
                action: 'CREATE_SALES_RECORD',
                subjectType: SalesRecord::class,
                subjectId: $salesRecord->id,
                description: "Mencatat penjualan {$code} (Total: Rp " . number_format($totalAmount, 0, ',', '.') . ")"
            );

            return redirect()->route('sales-records.show', $salesRecord->id)
                ->with('success', "Transaksi penjualan {$code} berhasil dicatat!");
        });
    }

    public function show(SalesRecord $salesRecord): Response
    {
        $salesRecord->load([
            'location',
            'creator',
            'items.product.baseUnit',
            'items.batch',
            'items.unit',
        ]);

        return Inertia::render('SalesRecords/Show', [
            'sale' => $salesRecord,
        ]);
    }
}
