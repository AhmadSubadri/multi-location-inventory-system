<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxController extends Controller
{
    public function index(): Response
    {
        $taxes = Tax::orderBy('name')->get();

        return Inertia::render('Taxes/Index', [
            'taxes' => $taxes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            Tax::query()->update(['is_default' => false]);
        }

        $tax = Tax::create($validated);

        ActivityLog::log(
            action: 'CREATE_TAX',
            subjectType: Tax::class,
            subjectId: $tax->id,
            description: "Membuat pajak: {$tax->name} ({$tax->percent}%)"
        );

        return back()->with('success', 'Pajak berhasil ditambahkan.');
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            Tax::where('id', '!=', $tax->id)->update(['is_default' => false]);
        }

        $tax->update($validated);

        ActivityLog::log(
            action: 'UPDATE_TAX',
            subjectType: Tax::class,
            subjectId: $tax->id,
            description: "Mengubah pajak: {$tax->name}"
        );

        return back()->with('success', 'Pajak berhasil diperbarui.');
    }

    public function destroy(Tax $tax)
    {
        $name = $tax->name;
        $tax->delete();

        ActivityLog::log(
            action: 'DELETE_TAX',
            subjectType: Tax::class,
            subjectId: $tax->id,
            description: "Menghapus pajak: {$name}"
        );

        return back()->with('success', 'Pajak berhasil dihapus.');
    }
}
