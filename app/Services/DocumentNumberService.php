<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate a document number based on configured format.
     *
     * @param string $type Document type: goods_receipt, stock_transfer, sales_record, return, stock_opname
     * @return string Generated document number
     */
    public function generate(string $type): string
    {
        $prefix = $this->getPrefix($type);
        $resetMode = Setting::getValue('doc_number_reset_mode', 'monthly'); // monthly or yearly
        $now = now();

        // Build the counter key based on reset mode
        $counterKey = match ($resetMode) {
            'yearly' => "{$type}_{$now->year}",
            default => "{$type}_{$now->year}_{$now->month}",
        };

        // Get and increment counter atomically
        $counter = DB::table('settings')
            ->where('key', "counter_{$counterKey}")
            ->lockForUpdate()
            ->first();

        if ($counter) {
            $nextNumber = (int) $counter->value + 1;
            DB::table('settings')
                ->where('key', "counter_{$counterKey}")
                ->update(['value' => $nextNumber]);
        } else {
            $nextNumber = 1;
            DB::table('settings')->insert([
                'key' => "counter_{$counterKey}",
                'value' => '1',
                'group' => 'counters',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Format: PREFIX-YYYY/MM/XXXX or PREFIX-YYYY/XXXX
        $paddedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return match ($resetMode) {
            'yearly' => "{$prefix}-{$now->year}/{$paddedNumber}",
            default => "{$prefix}-{$now->year}/{$now->format('m')}/{$paddedNumber}",
        };
    }

    /**
     * Get the prefix for a document type.
     */
    private function getPrefix(string $type): string
    {
        return match ($type) {
            'goods_receipt' => Setting::getValue('prefix_goods_receipt', 'GR'),
            'stock_transfer' => Setting::getValue('prefix_stock_transfer', 'TRF'),
            'sales_record' => Setting::getValue('prefix_sales_record', 'SLS'),
            'return' => Setting::getValue('prefix_return', 'RTN'),
            'stock_opname' => Setting::getValue('prefix_stock_opname', 'ADJ'),
            default => 'DOC',
        };
    }
}
