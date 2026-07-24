<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['customer_to_store', 'store_to_warehouse', 'warehouse_to_supplier']);
            $table->foreignId('location_id')->constrained(); // lokasi utama (toko/gudang yang menerima/mengirim)
            $table->foreignId('related_location_id')->nullable()->constrained('locations'); // lokasi terkait (gudang tujuan retur, dsb)
            $table->foreignId('supplier_id')->nullable()->constrained(); // untuk retur ke supplier
            $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('draft');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['location_id', 'type', 'status']);
            $table->index('created_at');
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches');
            $table->decimal('qty', 15, 2);
            $table->enum('condition', ['good', 'damaged'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
    }
};
