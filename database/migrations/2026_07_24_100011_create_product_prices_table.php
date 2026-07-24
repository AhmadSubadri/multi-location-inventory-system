<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('price_type', ['purchase', 'retail', 'wholesale']);
            $table->integer('min_qty')->default(1);
            $table->decimal('price', 15, 2);
            $table->timestamps();

            $table->index(['product_id', 'price_type']);
            $table->index(['product_id', 'location_id', 'price_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
