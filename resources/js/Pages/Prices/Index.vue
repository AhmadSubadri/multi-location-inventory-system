<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    prices: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const priceType = ref(props.filters.price_type || '');

const handleFilter = () => {
    router.get(
        route('prices.index'),
        { search: search.value, price_type: priceType.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Daftar Harga Produk" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Daftar Harga Produk (Multi-Tier)</h1>
                <p class="text-xs text-surface-500 mt-1">Harga beli, eceran, dan grosir untuk seluruh produk.</p>
            </div>
            <Link :href="route('products.index')" class="btn-secondary">
                <i class="fa-solid fa-boxes-stacked"></i> Kelola via Master Produk
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap items-center gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari SKU atau nama produk..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="priceType" @change="handleFilter" class="form-select text-xs w-48">
                    <option value="">-- Semua Tipe Harga --</option>
                    <option value="purchase">Harga Beli (HPP)</option>
                    <option value="retail">Harga Eceran (Retail)</option>
                    <option value="wholesale">Harga Grosir (Wholesale)</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Tipe Harga</th>
                            <th>Minimum Qty</th>
                            <th>Harga Nominal</th>
                            <th>Keterangan Lokasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in prices.data" :key="p.id">
                            <td class="font-bold text-surface-900 dark:text-white">
                                {{ p.product?.name }}
                                <span class="block text-[11px] font-mono font-normal text-surface-400">SKU: {{ p.product?.sku }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge capitalize font-semibold"
                                    :class="{
                                        'badge-warning': p.price_type === 'purchase',
                                        'badge-success': p.price_type === 'retail',
                                        'badge-info': p.price_type === 'wholesale',
                                    }"
                                >
                                    {{ p.price_type }}
                                </span>
                            </td>
                            <td class="text-xs">
                                {{ $number(p.min_qty) }} {{ p.product?.base_unit?.symbol }}
                            </td>
                            <td class="font-extrabold text-accent-600 dark:text-accent-400">
                                {{ $currency(p.price) }}
                            </td>
                            <td class="text-xs text-surface-500">
                                {{ p.location?.name || 'Semua Lokasi' }}
                            </td>
                            <td class="text-right">
                                <Link :href="route('products.edit', p.product_id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="prices.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">
                                Belum ada daftar harga.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="prices.links" />
        </div>
    </AuthenticatedLayout>
</template>
