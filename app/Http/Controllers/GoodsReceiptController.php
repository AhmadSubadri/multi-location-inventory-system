<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\DocumentNumberService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected DocumentNumberService $documentNumberService,
        protected StockLedgerService $stockLedgerService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $user = $request->user();
        $activeLocationId = session('active_location_id', $user->locations->first()?->id);

        $receipts = GoodsReceipt::with(['supplier', 'location', 'creator'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where('location_id', $activeLocationId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('GoodsReceipts/Index', [
            'receipts' => $receipts,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GoodsReceipts/Form', [
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
            'products' => Product::with(['baseUnit', 'unitConversions.fromUnit'])->active()->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.production_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date', 'after_or_equal:production_date'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $code = $this->documentNumberService->generate('goods_receipt');

            $receipt = GoodsReceipt::create([
                'code' => $code,
                'supplier_id' => $validated['supplier_id'],
                'location_id' => $validated['location_id'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'received_at' => $validated['received_at'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_id' => $item['unit_id'],
                    'unit_price' => $item['unit_price'],
                    'batch_number' => $item['batch_number'],
                    'production_date' => $item['production_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            ActivityLog::log(
                action: 'CREATE_GOODS_RECEIPT',
                subjectType: GoodsReceipt::class,
                subjectId: $receipt->id,
                description: "Membuat draft penerimaan barang: {$code}"
            );

            return redirect()->route('goods-receipts.show', $receipt->id)
                ->with('success', "Dokumen penerimaan barang {$code} berhasil dibuat (Status: Draft).");
        });
    }

    public function show(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load([
            'supplier',
            'location',
            'creator',
            'approver',
            'items.product.baseUnit',
            'items.unit',
        ]);

        return Inertia::render('GoodsReceipts/Show', [
            'receipt' => $goodsReceipt,
        ]);
    }

    /**
     * Finalize & Approve Goods Receipt: Write stock to batches and record in stock_ledgers.
     */
    public function approve(GoodsReceipt $goodsReceipt)
    {
        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Dokumen penerimaan barang sudah di-approve sebelumnya.');
        }

        return DB::transaction(function () use ($goodsReceipt) {
            $goodsReceipt->load('items.product', 'supplier');
            $supplierName = $goodsReceipt->supplier?->name ?? 'Supplier';

            foreach ($goodsReceipt->items as $item) {
                // Find or create batch for this product at this location
                $batch = ProductBatch::create([
                    'product_id' => $item->product_id,
                    'location_id' => $goodsReceipt->location_id,
                    'batch_number' => $item->batch_number,
                    'production_date' => $item->production_date,
                    'expiry_date' => $item->expiry_date,
                    'initial_qty' => $item->qty,
                    'remaining_qty' => $item->qty,
                ]);

                // Record stock ledger entry IN
                $this->stockLedgerService->recordEntry(
                    productId: $item->product_id,
                    batchId: $batch->id,
                    locationId: $goodsReceipt->location_id,
                    type: 'IN',
                    qty: $item->qty,
                    referenceType: GoodsReceipt::class,
                    referenceId: $goodsReceipt->id,
                    userId: auth()->id(),
                    notes: "Penerimaan barang dari supplier {$supplierName} (Dokumen {$goodsReceipt->code})"
                );
            }

            $goodsReceipt->update([
                'status' => 'received',
                'approved_by' => auth()->id(),
            ]);

            ActivityLog::log(
                action: 'APPROVE_GOODS_RECEIPT',
                subjectType: GoodsReceipt::class,
                subjectId: $goodsReceipt->id,
                description: "Menyetujui & memproses stok penerimaan barang: {$goodsReceipt->code}"
            );

            return back()->with('success', "Penerimaan barang {$goodsReceipt->code} berhasil disetujui. Stok telah masuk ke gudang!");
        });
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Dokumen yang telah disetujui/masuk stok tidak dapat dihapus.');
        }

        $code = $goodsReceipt->code;
        $goodsReceipt->delete();

        ActivityLog::log(
            action: 'DELETE_GOODS_RECEIPT',
            subjectType: GoodsReceipt::class,
            subjectId: $goodsReceipt->id,
            description: "Menghapus draft penerimaan barang: {$code}"
        );

        return redirect()->route('goods-receipts.index')->with('success', 'Draft penerimaan barang berhasil dihapus.');
    }
}
