<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_records', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('location_id')->constrained();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact', 50)->nullable();
            $table->enum('record_type', ['individual', 'daily_recap'])->default('individual');
            $table->date('sold_at');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['location_id', 'sold_at']);
            $table->index('created_at');
        });

        Schema::create('sales_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches');
            $table->decimal('qty', 15, 2);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_record_items');
        Schema::dropIfExists('sales_records');
    }
};
