<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    units: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const isHazardous = ref(props.filters.is_hazardous ?? '');

const applyFilters = () => {
    router.get(
        route('products.index'),
        {
            search: search.value,
            category_id: categoryId.value,
            is_hazardous: isHazardous.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    categoryId.value = '';
    isHazardous.value = '';
    applyFilters();
};

const deleteProduct = (p) => {
    if (confirm(`Apakah Anda yakin ingin menghapus produk "${p.name}" (${p.sku})?`)) {
        router.delete(route('products.destroy', p.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Produk" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Data Produk Sarana Pertanian</h1>
                <p class="text-xs text-surface-500 mt-1">Daftar produk, bahan aktif, izin registrasi, dan kategori.</p>
            </div>
            <Link :href="route('products.create')" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Produk Baru</span>
            </Link>
        </div>

        <div class="card overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <!-- Search -->
                <div class="relative">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="SKU, Nama, Bahan Aktif..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <!-- Category -->
                <div>
                    <select v-model="categoryId" @change="applyFilters" class="form-select text-xs">
                        <option value="">-- Semua Kategori --</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <!-- B3 / Hazardous Filter -->
                <div>
                    <select v-model="isHazardous" @change="applyFilters" class="form-select text-xs">
                        <option value="">-- Filter B3 / Berbahaya --</option>
                        <option value="1">B3 / Bahan Berbahaya</option>
                        <option value="0">Biasa (Non-B3)</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button @click="applyFilters" class="btn-secondary btn-sm flex-1">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <button @click="resetFilters" class="btn-secondary btn-sm text-surface-500">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SKU / Produk</th>
                            <th>Kategori</th>
                            <th>Bahan Aktif</th>
                            <th>Satuan Dasar</th>
                            <th>Tag B3</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-500 flex items-center justify-center font-mono font-bold text-xs shrink-0 overflow-hidden">
                                        <img v-if="p.image_path" :src="`/storage/${p.image_path}`" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-box text-surface-400"></i>
                                    </div>
                                    <div>
                                        <Link :href="route('products.show', p.id)" class="font-bold text-surface-900 dark:text-white hover:text-primary-600">
                                            {{ p.name }}
                                        </Link>
                                        <div class="flex items-center gap-2 text-[11px] text-surface-400">
                                            <span class="font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded text-surface-700 dark:text-surface-300 font-medium">SKU: {{ p.sku }}</span>
                                            <span v-if="p.brand" class="text-surface-500">• {{ p.brand }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ p.category?.name || '-' }}
                                </span>
                            </td>
                            <td class="text-xs text-surface-600 dark:text-surface-300">
                                {{ p.active_ingredient || '-' }}
                            </td>
                            <td>
                                <span class="badge badge-neutral">
                                    {{ p.base_unit?.name }} ({{ p.base_unit?.symbol }})
                                </span>
                            </td>
                            <td>
                                <span v-if="p.is_hazardous" class="badge badge-danger">
                                    <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px]"></i> B3 / Toxic
                                </span>
                                <span v-else class="badge badge-neutral text-surface-400">
                                    Aman
                                </span>
                            </td>
                            <td class="text-right space-x-1">
                                <Link :href="route('products.show', p.id)" class="btn-secondary btn-sm" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </Link>
                                <Link :href="route('products.edit', p.id)" class="btn-secondary btn-sm" title="Edit Produk">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </Link>
                                <button @click="deleteProduct(p)" class="btn-danger btn-sm" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="6" class="text-center py-10 text-surface-400 text-xs">
                                Tidak ada data produk yang memenuhi kriteria pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="products.links" />
        </div>
    </AuthenticatedLayout>
</template>
