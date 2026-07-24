<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $locations = Location::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            })
            ->orderBy('is_main_source', 'desc')
            ->orderBy('code', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:locations,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:warehouse,store'],
            'is_main_source' => ['boolean'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['is_main_source'])) {
            Location::query()->update(['is_main_source' => false]);
        }

        $location = Location::create($validated);

        ActivityLog::log(
            action: 'CREATE_LOCATION',
            subjectType: Location::class,
            subjectId: $location->id,
            newValues: $validated,
            description: "Membuat lokasi baru: {$location->name} ({$location->code})"
        );

        return back()->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:locations,code,' . $location->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:warehouse,store'],
            'is_main_source' => ['boolean'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['is_main_source'])) {
            Location::where('id', '!=', $location->id)->update(['is_main_source' => false]);
        }

        $oldValues = $location->toArray();
        $location->update($validated);

        ActivityLog::log(
            action: 'UPDATE_LOCATION',
            subjectType: Location::class,
            subjectId: $location->id,
            oldValues: $oldValues,
            newValues: $validated,
            description: "Mengubah lokasi: {$location->name}"
        );

        return back()->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        if ($location->is_main_source) {
            return back()->with('error', 'Gudang Utama (sumber utama) tidak boleh dihapus.');
        }

        if ($location::has('batches')) {
            // Check if there are active batches
            $hasStock = $location->batches()->where('remaining_qty', '>', 0)->exists();
            if ($hasStock) {
                return back()->with('error', 'Lokasi tidak dapat dihapus karena masih menyimpan stok produk.');
            }
        }

        $name = $location->name;
        $location->delete();

        ActivityLog::log(
            action: 'DELETE_LOCATION',
            subjectType: Location::class,
            subjectId: $location->id,
            description: "Menghapus lokasi: {$name}"
        );

        return back()->with('success', 'Lokasi berhasil dihapus.');
    }
}
