<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $units = Unit::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
            'symbol' => ['required', 'string', 'max:50', 'unique:units,symbol'],
        ]);

        $unit = Unit::create($validated);

        ActivityLog::log(
            action: 'CREATE_UNIT',
            subjectType: Unit::class,
            subjectId: $unit->id,
            newValues: $validated,
            description: "Membuat satuan: {$unit->name} ({$unit->symbol})"
        );

        return back()->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name,' . $unit->id],
            'symbol' => ['required', 'string', 'max:50', 'unique:units,symbol,' . $unit->id],
        ]);

        $oldValues = $unit->toArray();
        $unit->update($validated);

        ActivityLog::log(
            action: 'UPDATE_UNIT',
            subjectType: Unit::class,
            subjectId: $unit->id,
            oldValues: $oldValues,
            newValues: $validated,
            description: "Mengubah satuan: {$unit->name}"
        );

        return back()->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena masih digunakan sebagai satuan dasar produk.');
        }

        $unitName = $unit->name;
        $unit->delete();

        ActivityLog::log(
            action: 'DELETE_UNIT',
            subjectType: Unit::class,
            subjectId: $unit->id,
            description: "Menghapus satuan: {$unitName}"
        );

        return back()->with('success', 'Satuan berhasil dihapus.');
    }
}
