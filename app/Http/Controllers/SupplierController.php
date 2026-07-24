<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $suppliers = Supplier::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($validated);

        ActivityLog::log(
            action: 'CREATE_SUPPLIER',
            subjectType: Supplier::class,
            subjectId: $supplier->id,
            newValues: $validated,
            description: "Membuat supplier/pemasok: {$supplier->name}"
        );

        return back()->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        ActivityLog::log(
            action: 'UPDATE_SUPPLIER',
            subjectType: Supplier::class,
            subjectId: $supplier->id,
            oldValues: $oldValues,
            newValues: $validated,
            description: "Mengubah supplier/pemasok: {$supplier->name}"
        );

        return back()->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplierName = $supplier->name;
        $supplier->delete();

        ActivityLog::log(
            action: 'DELETE_SUPPLIER',
            subjectType: Supplier::class,
            subjectId: $supplier->id,
            description: "Menghapus supplier: {$supplierName}"
        );

        return back()->with('success', 'Supplier berhasil dihapus.');
    }
}
