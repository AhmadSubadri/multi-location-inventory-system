<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Setting;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\DocumentNumberService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController extends Controller
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

        $transfers = StockTransfer::with(['fromLocation', 'toLocation', 'requester', 'approver'])
            ->when(!$user->isSuperAdmin() && !$user->isOwner() && $activeLocationId, function ($q) use ($activeLocationId) {
                $q->where(function ($query) use ($activeLocationId) {
                    $query->where('from_location_id', $activeLocationId)
                          ->orWhere('to_location_id', $activeLocationId);
                });
            })
            ->when($search, fn($q) => $q->where('code', 'like', "%{$search}%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function create(): Response
    {
        $enableStoreToStore = Setting::getValue('enable_store_to_store_transfer', '0') === '1';

        return Inertia::render('StockTransfers/Form', [
            'locations' => Location::active()->orderBy('name')->get(),
            'products' => Product::with(['baseUnit', 'batches' => fn($b) => $b->available()->fefo()])
                ->active()
                ->orderBy('name')
                ->get(),
            'enableStoreToStore' => $enableStoreToStore,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => ['required', 'integer', 'exists:locations,id'],
            'to_location_id' => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.batch_id' => ['required', 'integer', 'exists:product_batches,id'],
            'items.*.qty_sent' => ['required', 'numeric', 'gt:0'],
        ]);

        $fromLocation = Location::findOrFail($validated['from_location_id']);
        $toLocation = Location::findOrFail($validated['to_location_id']);
        $enableStoreToStore = Setting::getValue('enable_store_to_store_transfer', '0') === '1';

        if ($fromLocation->type === 'store' && $toLocation->type === 'store' && !$enableStoreToStore) {
            return back()->with('error', 'Transfer antar toko (Store-to-Store) saat ini dinonaktifkan dalam sistem.');
        }

        return DB::transaction(function () use ($validated, $fromLocation, $toLocation) {
            $code = $this->documentNumberService->generate('stock_transfer');

            $transfer = StockTransfer::create([
                'code' => $code,
                'from_location_id' => $validated['from_location_id'],
                'to_location_id' => $validated['to_location_id'],
                'status' => 'submitted',
                'notes' => $validated['notes'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'qty_sent' => $item['qty_sent'],
                ]);
            }

            ActivityLog::log(
                action: 'CREATE_STOCK_TRANSFER',
                subjectType: StockTransfer::class,
                subjectId: $transfer->id,
                description: "Mengajukan transfer stok {$code} dari {$fromLocation->name} ke {$toLocation->name}"
            );

            return redirect()->route('stock-transfers.show', $transfer->id)
                ->with('success', "Permintaan transfer stok {$code} berhasil diajukan (Status: Submitted).");
        });
    }

    public function show(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load([
            'fromLocation',
            'toLocation',
            'requester',
            'approver',
            'items.product.baseUnit',
            'items.batch',
        ]);

        return Inertia::render('StockTransfers/Show', [
            'transfer' => $stockTransfer,
        ]);
    }

    public function approve(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'submitted') {
            return back()->with('error', 'Transfer stok tidak berada pada status pengajuan.');
        }

        $stockTransfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        ActivityLog::log(
            action: 'APPROVE_STOCK_TRANSFER',
            subjectType: StockTransfer::class,
            subjectId: $stockTransfer->id,
            description: "Menyetujui transfer stok {$stockTransfer->code}"
        );

        return back()->with('success', "Transfer stok {$stockTransfer->code} telah disetujui. Siap untuk dikirim.");
    }

    public function ship(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'approved') {
            return back()->with('error', 'Dokumen transfer belum disetujui.');
        }

        return DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items', 'toLocation');
            $toLocationName = $stockTransfer->toLocation?->name ?? 'Tujuan';

            foreach ($stockTransfer->items as $item) {
                $this->stockLedgerService->recordEntry(
                    productId: $item->product_id,
                    batchId: $item->batch_id,
                    locationId: $stockTransfer->from_location_id,
                    type: 'TRANSFER_OUT',
                    qty: $item->qty_sent,
                    referenceType: StockTransfer::class,
                    referenceId: $stockTransfer->id,
                    userId: auth()->id(),
                    notes: "Pengiriman transfer stok ke {$toLocationName} (Dokumen {$stockTransfer->code})"
                );
            }

            $stockTransfer->update([
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            ActivityLog::log(
                action: 'SHIP_STOCK_TRANSFER',
                subjectType: StockTransfer::class,
                subjectId: $stockTransfer->id,
                description: "Mengirim transfer stok {$stockTransfer->code} (Status: Shipped / In-Transit)"
            );

            return back()->with('success', "Transfer stok {$stockTransfer->code} berhasil dikirim! Stok asal dipotong, status kini In-Transit.");
        });
    }

    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'shipped') {
            return back()->with('error', 'Dokumen transfer belum dikirim (harus berstatus shipped/in-transit).');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:stock_transfer_items,id'],
            'items.*.qty_received' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated, $stockTransfer) {
            $stockTransfer->load('items.batch', 'fromLocation');
            $fromLocationName = $stockTransfer->fromLocation?->name ?? 'Asal';

            foreach ($validated['items'] as $recvItem) {
                $item = StockTransferItem::findOrFail($recvItem['id']);
                $qtyReceived = (float) $recvItem['qty_received'];

                $item->update(['qty_received' => $qtyReceived]);

                if ($qtyReceived > 0) {
                    $destBatch = ProductBatch::create([
                        'product_id' => $item->product_id,
                        'location_id' => $stockTransfer->to_location_id,
                        'batch_number' => $item->batch->batch_number,
                        'production_date' => $item->batch->production_date,
                        'expiry_date' => $item->batch->expiry_date,
                        'initial_qty' => $qtyReceived,
                        'remaining_qty' => $qtyReceived,
                    ]);

                    $this->stockLedgerService->recordEntry(
                        productId: $item->product_id,
                        batchId: $destBatch->id,
                        locationId: $stockTransfer->to_location_id,
                        type: 'TRANSFER_IN',
                        qty: $qtyReceived,
                        referenceType: StockTransfer::class,
                        referenceId: $stockTransfer->id,
                        userId: auth()->id(),
                        notes: "Penerimaan transfer stok dari {$fromLocationName} (Dokumen {$stockTransfer->code})"
                    );
                }
            }

            $stockTransfer->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            ActivityLog::log(
                action: 'RECEIVE_STOCK_TRANSFER',
                subjectType: StockTransfer::class,
                subjectId: $stockTransfer->id,
                description: "Menerima transfer stok {$stockTransfer->code} di lokasi tujuan"
            );

            return back()->with('success', "Transfer stok {$stockTransfer->code} telah diterima & stok berhasil ditambahkan ke lokasi tujuan!");
        });
    }
}
