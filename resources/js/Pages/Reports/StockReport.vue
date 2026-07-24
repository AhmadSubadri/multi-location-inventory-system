<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    locations: Array,
    filters: Object,
});

const locationId = ref(props.filters.location_id || '');

const handleFilter = () => {
    router.get(route('reports.stock'), { location_id: locationId.value }, { preserveState: true, replace: true });
};

const printReport = () => window.print();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Stok & Valuation" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Nilai Stok & Valuation Inventory</h1>
                <p class="text-xs text-surface-500 mt-1">Kuantitas stok dan total estimasi aset berdasarkan HPP Beli / Harga Jual.</p>
            </div>
            <button @click="printReport" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 print:hidden">
                <select v-model="locationId" @change="handleFilter" class="form-select text-xs w-60">
                    <option value="">-- Semua Lokasi (Global) --</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SKU / Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-right">Sisa Stok</th>
                            <th class="text-right">HPP Beli (Rp)</th>
                            <th class="text-right">Total Valuasi HPP</th>
                            <th class="text-right">Total Valuasi Ritel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id">
                            <td>
                                <span class="font-bold text-surface-900 dark:text-white">{{ p.name }}</span>
                                <span class="block text-[10px] font-mono text-surface-400">SKU: {{ p.sku }}</span>
                            </td>
                            <td class="text-xs">{{ p.category }}</td>
                            <td class="text-right font-extrabold text-primary-600">{{ $number(p.total_qty) }} {{ p.base_unit }}</td>
                            <td class="text-right text-xs">{{ $currency(p.purchase_price) }}</td>
                            <td class="text-right font-bold text-surface-900 dark:text-white">{{ $currency(p.total_valuation_hpp) }}</td>
                            <td class="text-right font-bold text-accent-600">{{ $currency(p.total_valuation_retail) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="products.links" />
        </div>
    </AuthenticatedLayout>
</template>
