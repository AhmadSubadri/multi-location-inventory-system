<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches');
            $table->foreignId('location_id')->constrained();
            $table->enum('type', [
                'IN',
                'OUT_SALE',
                'TRANSFER_OUT',
                'TRANSFER_IN',
                'RETURN_IN',
                'RETURN_OUT',
                'ADJUSTMENT',
            ]);
            $table->decimal('qty', 15, 2); // positive for IN, negative for OUT
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('reference_type')->nullable(); // morph: GoodsReceipt, StockTransfer, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Performance indexes
            $table->index(['product_id', 'location_id', 'created_at'], 'idx_ledger_product_location_date');
            $table->index(['reference_type', 'reference_id'], 'idx_ledger_reference');
            $table->index(['location_id', 'type', 'created_at'], 'idx_ledger_location_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
