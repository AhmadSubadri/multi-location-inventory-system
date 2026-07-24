<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductStockSetting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $isHazardous = $request->input('is_hazardous');

        $products = Product::with(['category', 'baseUnit', 'prices', 'suppliers'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('active_ingredient', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($isHazardous !== null && $isHazardous !== '', fn($q) => $q->where('is_hazardous', (bool) $isHazardous))
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        $categories = ProductCategory::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'is_hazardous' => $isHazardous,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => ProductCategory::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'base_unit_id' => ['required', 'integer', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'is_hazardous' => ['boolean'],
            'hazardous_notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB max
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['integer', 'exists:suppliers,id'],
            // Prices
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.price_type' => ['required', 'string', 'in:purchase,retail,wholesale'],
            'prices.*.min_qty' => ['required', 'numeric', 'min:1'],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
            'prices.*.location_id' => ['nullable', 'integer', 'exists:locations,id'],
            // Unit Conversions
            'conversions' => ['nullable', 'array'],
            'conversions.*.from_unit_id' => ['required', 'integer', 'exists:units,id'],
            'conversions.*.conversion_factor' => ['required', 'numeric', 'gt:0'],
            // Min stocks
            'stock_settings' => ['nullable', 'array'],
            'stock_settings.*.location_id' => ['required', 'integer', 'exists:locations,id'],
            'stock_settings.*.min_stock' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'base_unit_id' => $validated['base_unit_id'],
                'description' => $validated['description'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'active_ingredient' => $validated['active_ingredient'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'is_hazardous' => $validated['is_hazardous'] ?? false,
                'hazardous_notes' => $validated['hazardous_notes'] ?? null,
                'image_path' => $imagePath,
            ]);

            // Save prices
            foreach ($validated['prices'] as $price) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price_type' => $price['price_type'],
                    'min_qty' => $price['min_qty'],
                    'price' => $price['price'],
                    'location_id' => $price['location_id'] ?? null,
                ]);
            }

            // Save unit conversions
            if (!empty($validated['conversions'])) {
                foreach ($validated['conversions'] as $conv) {
                    UnitConversion::create([
                        'product_id' => $product->id,
                        'from_unit_id' => $conv['from_unit_id'],
                        'to_unit_id' => $validated['base_unit_id'],
                        'conversion_factor' => $conv['conversion_factor'],
                    ]);
                }
            }

            // Save stock settings
            if (!empty($validated['stock_settings'])) {
                foreach ($validated['stock_settings'] as $ss) {
                    ProductStockSetting::create([
                        'product_id' => $product->id,
                        'location_id' => $ss['location_id'],
                        'min_stock' => $ss['min_stock'],
                    ]);
                }
            }

            // Attach suppliers
            if (!empty($validated['suppliers'])) {
                $product->suppliers()->attach($validated['suppliers']);
            }

            ActivityLog::log(
                action: 'CREATE_PRODUCT',
                subjectType: Product::class,
                subjectId: $product->id,
                description: "Membuat master produk: {$product->name} (SKU: {$product->sku})"
            );

            return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
        });
    }

    public function show(Product $product): Response
    {
        $product->load([
            'category',
            'baseUnit',
            'prices.location',
            'unitConversions.fromUnit',
            'unitConversions.toUnit',
            'stockSettings.location',
            'batches.location',
            'suppliers',
        ]);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load([
            'prices',
            'unitConversions',
            'stockSettings',
            'suppliers',
        ]);

        return Inertia::render('Products/Form', [
            'product' => $product,
            'categories' => ProductCategory::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'base_unit_id' => ['required', 'integer', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'is_hazardous' => ['boolean'],
            'hazardous_notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['integer', 'exists:suppliers,id'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.price_type' => ['required', 'string', 'in:purchase,retail,wholesale'],
            'prices.*.min_qty' => ['required', 'numeric', 'min:1'],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
            'prices.*.location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'conversions' => ['nullable', 'array'],
            'conversions.*.from_unit_id' => ['required', 'integer', 'exists:units,id'],
            'conversions.*.conversion_factor' => ['required', 'numeric', 'gt:0'],
            'stock_settings' => ['nullable', 'array'],
            'stock_settings.*.location_id' => ['required', 'integer', 'exists:locations,id'],
            'stock_settings.*.min_stock' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $product, $validated) {
            $imagePath = $product->image_path;
            if ($request->hasFile('image')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product->update([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'base_unit_id' => $validated['base_unit_id'],
                'description' => $validated['description'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'active_ingredient' => $validated['active_ingredient'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'is_hazardous' => $validated['is_hazardous'] ?? false,
                'hazardous_notes' => $validated['hazardous_notes'] ?? null,
                'image_path' => $imagePath,
            ]);

            // Sync prices
            $product->prices()->delete();
            foreach ($validated['prices'] as $price) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price_type' => $price['price_type'],
                    'min_qty' => $price['min_qty'],
                    'price' => $price['price'],
                    'location_id' => $price['location_id'] ?? null,
                ]);
            }

            // Sync unit conversions
            $product->unitConversions()->delete();
            if (!empty($validated['conversions'])) {
                foreach ($validated['conversions'] as $conv) {
                    UnitConversion::create([
                        'product_id' => $product->id,
                        'from_unit_id' => $conv['from_unit_id'],
                        'to_unit_id' => $validated['base_unit_id'],
                        'conversion_factor' => $conv['conversion_factor'],
                    ]);
                }
            }

            // Sync stock settings
            $product->stockSettings()->delete();
            if (!empty($validated['stock_settings'])) {
                foreach ($validated['stock_settings'] as $ss) {
                    ProductStockSetting::create([
                        'product_id' => $product->id,
                        'location_id' => $ss['location_id'],
                        'min_stock' => $ss['min_stock'],
                    ]);
                }
            }

            // Sync suppliers
            $product->suppliers()->sync($validated['suppliers'] ?? []);

            ActivityLog::log(
                action: 'UPDATE_PRODUCT',
                subjectType: Product::class,
                subjectId: $product->id,
                description: "Mengubah master produk: {$product->name}"
            );

            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
        });
    }

    public function destroy(Product $product)
    {
        $hasStock = $product->batches()->where('remaining_qty', '>', 0)->exists();
        if ($hasStock) {
            return back()->with('error', 'Produk tidak dapat dihapus karena masih memiliki sisa stok di gudang/toko.');
        }

        $productName = $product->name;
        $product->delete();

        ActivityLog::log(
            action: 'DELETE_PRODUCT',
            subjectType: Product::class,
            subjectId: $product->id,
            description: "Menghapus produk: {$productName}"
        );

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}
