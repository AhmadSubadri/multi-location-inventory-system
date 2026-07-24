<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $categories = ProductCategory::with('parent', 'children')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        $allCategories = ProductCategory::orderBy('name', 'asc')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'allCategories' => $allCategories,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name'],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
        ]);

        $category = ProductCategory::create($validated);

        ActivityLog::log(
            action: 'CREATE_CATEGORY',
            subjectType: ProductCategory::class,
            subjectId: $category->id,
            newValues: $validated,
            description: "Membuat kategori produk: {$category->name}"
        );

        return back()->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name,' . $category->id],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id', 'different:' . $category->id],
            'description' => ['nullable', 'string'],
        ]);

        $oldValues = $category->toArray();
        $category->update($validated);

        ActivityLog::log(
            action: 'UPDATE_CATEGORY',
            subjectType: ProductCategory::class,
            subjectId: $category->id,
            oldValues: $oldValues,
            newValues: $validated,
            description: "Mengubah kategori produk: {$category->name}"
        );

        return back()->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena memiliki sub-kategori.');
        }

        $categoryName = $category->name;
        $category->delete();

        ActivityLog::log(
            action: 'DELETE_CATEGORY',
            subjectType: ProductCategory::class,
            subjectId: $category->id,
            description: "Menghapus kategori produk: {$categoryName}"
        );

        return back()->with('success', 'Kategori produk berhasil dihapus.');
    }
}
