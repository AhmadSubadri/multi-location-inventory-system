<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountController extends Controller
{
    public function index(Request $request): Response
    {
        $discounts = Discount::with('product')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Discounts/Index', [
            'discounts' => $discounts,
            'products' => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $discount = Discount::create($validated);

        ActivityLog::log(
            action: 'CREATE_DISCOUNT',
            subjectType: Discount::class,
            subjectId: $discount->id,
            description: "Membuat aturan diskon: {$discount->name}"
        );

        return back()->with('success', 'Aturan diskon berhasil dibuat.');
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $discount->update($validated);

        ActivityLog::log(
            action: 'UPDATE_DISCOUNT',
            subjectType: Discount::class,
            subjectId: $discount->id,
            description: "Mengubah aturan diskon: {$discount->name}"
        );

        return back()->with('success', 'Aturan diskon berhasil diperbarui.');
    }

    public function destroy(Discount $discount)
    {
        $name = $discount->name;
        $discount->delete();

        ActivityLog::log(
            action: 'DELETE_DISCOUNT',
            subjectType: Discount::class,
            subjectId: $discount->id,
            description: "Menghapus diskon: {$name}"
        );

        return back()->with('success', 'Diskon berhasil dihapus.');
    }
}
