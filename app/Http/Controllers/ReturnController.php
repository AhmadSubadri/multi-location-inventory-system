<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ReturnDocument;
use App\Models\ReturnItem;
use App\Models\Supplier;
use App\Services\DocumentNumberService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReturnController extends Controller
{
    public function __construct(
        protected DocumentNumberService $documentNumberService,
        protected StockLedgerService $stockLedgerService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $returns = ReturnDocument::with(['location', 'relatedLocation', 'supplier', 'creator'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where('location_id', $activeLocationId);
            })
            ->when($search, fn($q) => $q->where('code', 'like', "%{$search}%"))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Returns/Index', [
            'returns' => $returns,
            'filters' => ['search' => $search, 'type' => $type],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Returns/Form', [
            'locations' => Location::active()->orderBy('name')->get(),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'products' => Product::with(['baseUnit', 'batches' => fn($b) => $b->available()->fefo()])
                ->active()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:store_to_warehouse,warehouse_to_supplier,customer_to_store'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'related_location_id' => ['nullable', 'required_if:type,store_to_warehouse', 'integer', 'exists:locations,id'],
            'supplier_id' => ['nullable', 'required_if:type,warehouse_to_supplier', 'integer', 'exists:suppliers,id'],
            'reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.batch_id' => ['required', 'integer', 'exists:product_batches,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.condition' => ['required', 'string', 'in:good,damaged'],
        ]);

        return DB::transaction(function () use ($validated) {
            $code = $this->documentNumberService->generate('return');

            $returnDoc = ReturnDocument::create([
                'code' => $code,
                'type' => $validated['type'],
                'location_id' => $validated['location_id'],
                'related_location_id' => $validated['related_location_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'status' => 'approved', // Direct approval for returns
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                ReturnItem::create([
                    'return_id' => $returnDoc->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'qty' => $item['qty'],
                    'condition' => $item['condition'],
                ]);

                // Determine ledger movement type based on scenario
                $ledgerType = match ($validated['type']) {
                    'store_to_warehouse' => 'RETURN_OUT', // Store sends back to warehouse
                    'warehouse_to_supplier' => 'RETURN_OUT', // Warehouse sends back to supplier
                    'customer_to_store' => 'RETURN_IN', // Customer returns to store (adds back stock if good condition)
                };

                // Record stock ledger
                if ($validated['type'] === 'customer_to_store') {
                    // Only add back stock if item condition is good
                    if ($item['condition'] === 'good') {
                        $this->stockLedgerService->recordEntry(
                            productId: $item['product_id'],
                            batchId: $item['batch_id'],
                            locationId: $validated['location_id'],
                            type: 'RETURN_IN',
                            qty: $item['qty'],
                            referenceType: ReturnDocument::class,
                            referenceId: $returnDoc->id,
                            userId: auth()->id(),
                            notes: "Retur dari konsumen (kondisi baik) - Dokumen {$code}"
                        );
                    }
                } else {
                    // Deduct stock for outgoing return
                    $this->stockLedgerService->recordEntry(
                        productId: $item['product_id'],
                        batchId: $item['batch_id'],
                        locationId: $validated['location_id'],
                        type: 'RETURN_OUT',
                        qty: $item['qty'],
                        referenceType: ReturnDocument::class,
                        referenceId: $returnDoc->id,
                        userId: auth()->id(),
                        notes: "Retur keluar (Tipe: {$validated['type']}) - Dokumen {$code}"
                    );
                }
            }

            ActivityLog::log(
                action: 'CREATE_RETURN',
                subjectType: ReturnDocument::class,
                subjectId: $returnDoc->id,
                description: "Membuat dokumen retur {$code} (Tipe: {$validated['type']})"
            );

            return redirect()->route('returns.show', $returnDoc->id)
                ->with('success', "Dokumen retur barang {$code} berhasil diproses!");
        });
    }

    public function show(ReturnDocument $return): Response
    {
        $return->load([
            'location',
            'relatedLocation',
            'supplier',
            'creator',
            'items.product.baseUnit',
            'items.batch',
        ]);

        return Inertia::render('Returns/Show', [
            'returnDoc' => $return,
        ]);
    }
}
