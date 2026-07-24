<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockLedgerService
{
    /**
     * Record a stock movement entry in the ledger.
     * This is the ONLY place where stock changes should happen.
     *
     * @param int $productId
     * @param int|null $batchId
     * @param int $locationId
     * @param string $type (IN, OUT_SALE, TRANSFER_OUT, TRANSFER_IN, RETURN_IN, RETURN_OUT, ADJUSTMENT)
     * @param float $qty Absolute quantity (always positive). Direction is determined by $type.
     * @param string|null $referenceType Morph class name
     * @param int|null $referenceId
     * @param int $userId
     * @param string|null $notes
     * @return StockLedger
     *
     * @throws InvalidArgumentException if stock would go negative
     */
    public function recordEntry(
        int $productId,
        ?int $batchId,
        int $locationId,
        string $type,
        float $qty,
        ?string $referenceType,
        ?int $referenceId,
        int $userId,
        ?string $notes = null,
    ): StockLedger {
        return DB::transaction(function () use (
            $productId, $batchId, $locationId, $type, $qty,
            $referenceType, $referenceId, $userId, $notes
        ) {
            // Determine if this is an incoming or outgoing movement
            $isIncoming = $this->isIncomingType($type);

            // Calculate the signed qty
            $signedQty = $isIncoming ? abs($qty) : -abs($qty);

            // Get current balance for this product at this location
            $currentBalance = $this->getCurrentBalance($productId, $locationId);

            // Calculate new balance
            $newBalance = $currentBalance + $signedQty;

            // Validate: stock must not go negative for outgoing movements
            if (!$isIncoming && $newBalance < 0) {
                throw new InvalidArgumentException(
                    "Stok tidak mencukupi. Stok saat ini: {$currentBalance}, " .
                    "dibutuhkan: " . abs($qty) . ". Stok tidak boleh negatif."
                );
            }

            // Create the ledger entry
            $entry = StockLedger::create([
                'product_id' => $productId,
                'batch_id' => $batchId,
                'location_id' => $locationId,
                'type' => $type,
                'qty' => $signedQty,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            // Update batch remaining_qty if batch is specified
            if ($batchId) {
                $this->updateBatchQty($batchId, $signedQty, $isIncoming);
            }

            return $entry;
        });
    }

    /**
     * Record multiple stock entries in a single transaction.
     * Useful for transfers, opname adjustments, etc.
     *
     * @param array $entries Array of entry data arrays
     * @return array Array of StockLedger entries
     */
    public function recordMultipleEntries(array $entries): array
    {
        return DB::transaction(function () use ($entries) {
            $results = [];
            foreach ($entries as $entry) {
                $results[] = $this->recordEntry(
                    productId: $entry['product_id'],
                    batchId: $entry['batch_id'] ?? null,
                    locationId: $entry['location_id'],
                    type: $entry['type'],
                    qty: $entry['qty'],
                    referenceType: $entry['reference_type'] ?? null,
                    referenceId: $entry['reference_id'] ?? null,
                    userId: $entry['user_id'],
                    notes: $entry['notes'] ?? null,
                );
            }
            return $results;
        });
    }

    /**
     * Get the current stock balance for a product at a location.
     * Uses the last ledger entry's balance_after as the current balance.
     */
    public function getCurrentBalance(int $productId, int $locationId): float
    {
        // Sum remaining_qty from batches as the source of truth
        return (float) ProductBatch::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->sum('remaining_qty');
    }

    /**
     * Check if stock is sufficient for an outgoing movement.
     */
    public function hasEnoughStock(int $productId, int $locationId, float $qty): bool
    {
        return $this->getCurrentBalance($productId, $locationId) >= $qty;
    }

    /**
     * Check if a specific batch has enough stock.
     */
    public function hasBatchEnoughStock(int $batchId, float $qty): bool
    {
        $batch = ProductBatch::find($batchId);
        return $batch && $batch->remaining_qty >= $qty;
    }

    /**
     * Determine if a movement type is incoming (adds stock).
     */
    private function isIncomingType(string $type): bool
    {
        return in_array($type, ['IN', 'TRANSFER_IN', 'RETURN_IN']) ||
               ($type === 'ADJUSTMENT'); // Adjustment qty can be positive or negative
    }

    /**
     * Update the batch remaining quantity.
     */
    private function updateBatchQty(int $batchId, float $signedQty, bool $isIncoming): void
    {
        $batch = ProductBatch::lockForUpdate()->findOrFail($batchId);

        // For ADJUSTMENT type, the signedQty already has the correct sign
        $batch->remaining_qty += $signedQty;

        if ($batch->remaining_qty < 0) {
            throw new InvalidArgumentException(
                "Stok batch {$batch->batch_number} tidak mencukupi. " .
                "Sisa: {$batch->remaining_qty}"
            );
        }

        $batch->save();
    }

    /**
     * Get available batches for a product at a location, ordered by FEFO.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableBatches(int $productId, int $locationId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->available()
            ->fefo()
            ->get();
    }

    /**
     * Auto-select batches using FEFO for the given quantity.
     * Returns array of ['batch_id' => qty] pairs.
     */
    public function selectBatchesFEFO(int $productId, int $locationId, float $neededQty): array
    {
        $batches = $this->getAvailableBatches($productId, $locationId);
        $selections = [];
        $remaining = $neededQty;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $batch->remaining_qty);
            $selections[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'qty' => $take,
                'expiry_date' => $batch->expiry_date,
            ];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new InvalidArgumentException(
                "Stok tidak mencukupi. Dibutuhkan: {$neededQty}, " .
                "tersedia: " . ($neededQty - $remaining)
            );
        }

        return $selections;
    }
}
