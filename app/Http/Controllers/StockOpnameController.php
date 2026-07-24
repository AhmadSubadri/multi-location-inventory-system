<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\DocumentNumberService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockOpnameController extends Controller
{
    public function __construct(
        protected DocumentNumberService $documentNumberService,
        protected StockLedgerService $stockLedgerService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $opnames = StockOpname::with(['location', 'performer', 'approver'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where('location_id', $activeLocationId);
            })
            ->when($search, fn($q) => $q->where('code', 'like', "%{$search}%"))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('StockOpnames/Index', [
            'opnames' => $opnames,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $products = Product::with(['baseUnit', 'batches' => function ($b) use ($activeLocationId) {
            $b->where('location_id', $activeLocationId)->available();
        }])
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('StockOpnames/Form', [
            'locations' => Location::active()->orderBy('name')->get(),
            'products' => $products,
            'activeLocationId' => $activeLocationId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.batch_id' => ['required', 'integer', 'exists:product_batches,id'],
            'items.*.system_qty' => ['required', 'numeric', 'min:0'],
            'items.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $code = $this->documentNumberService->generate('stock_opname');

            $opname = StockOpname::create([
                'code' => $code,
                'location_id' => $validated['location_id'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'performed_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $difference = (float) $item['physical_qty'] - (float) $item['system_qty'];

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'system_qty' => $item['system_qty'],
                    'physical_qty' => $item['physical_qty'],
                    'difference' => $difference,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            ActivityLog::log(
                action: 'CREATE_STOCK_OPNAME',
                subjectType: StockOpname::class,
                subjectId: $opname->id,
                description: "Membuat draft stok opname fisik {$code}"
            );

            return redirect()->route('stock-opnames.show', $opname->id)
                ->with('success', "Draft stok opname {$code} berhasil dibuat.");
        });
    }

    public function show(StockOpname $stockOpname): Response
    {
        $stockOpname->load([
            'location',
            'performer',
            'approver',
            'items.product.baseUnit',
            'items.batch',
        ]);

        return Inertia::render('StockOpnames/Show', [
            'opname' => $stockOpname,
        ]);
    }

    /**
     * Approve & Finalize Stock Opname (Adjust stock in ledger)
     */
    public function approve(StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return back()->with('error', 'Stok opname sudah disetujui sebelumnya.');
        }

        return DB::transaction(function () use ($stockOpname) {
            $stockOpname->load('items');

            foreach ($stockOpname->items as $item) {
                if ($item->difference != 0) {
                    // Record adjustment entry in ledger
                    $this->stockLedgerService->recordEntry(
                        productId: $item->product_id,
                        batchId: $item->batch_id,
                        locationId: $stockOpname->location_id,
                        type: 'ADJUSTMENT',
                        qty: $item->difference, // Positive or negative
                        referenceType: StockOpname::class,
                        referenceId: $stockOpname->id,
                        userId: auth()->id(),
                        notes: "Penyesuaian Stok Opname Fisik (Dokumen {$stockOpname->code})"
                    );
                }
            }

            $stockOpname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            ActivityLog::log(
                action: 'APPROVE_STOCK_OPNAME',
                subjectType: StockOpname::class,
                subjectId: $stockOpname->id,
                description: "Menyetujui & memproses penyesuaian stok opname {$stockOpname->code}"
            );

            return back()->with('success', "Stok opname {$stockOpname->code} berhasil disetujui & penyesuaian stok telah disimpan ke ledger!");
        });
    }
}
