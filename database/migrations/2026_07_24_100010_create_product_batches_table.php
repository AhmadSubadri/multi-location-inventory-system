<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('initial_qty', 15, 2)->default(0);
            $table->decimal('remaining_qty', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['product_id', 'location_id']);
            $table->index('expiry_date');
            $table->index(['product_id', 'location_id', 'remaining_qty'], 'idx_batch_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
