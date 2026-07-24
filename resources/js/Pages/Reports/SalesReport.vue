<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
    summary: Object,
    locations: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const locationId = ref(props.filters.location_id || '');

const handleFilter = () => {
    router.get(
        route('reports.sales'),
        { start_date: startDate.value, end_date: endDate.value, location_id: locationId.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Penjualan" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Penjualan & Omzet</h1>
                <p class="text-xs text-surface-500 mt-1">Rekapitulasi omzet dan performa penjualan per periode.</p>
            </div>
            <button @click="() => window.print()" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="card p-5">
                <p class="text-xs text-surface-500 font-semibold">TOTAL TRANSAKSI</p>
                <h3 class="text-2xl font-extrabold text-surface-900 dark:text-white mt-1">{{ summary.total_sales_count }} Nota</h3>
            </div>
            <div class="card p-5">
                <p class="text-xs text-surface-500 font-semibold">TOTAL OMZET PENJUALAN</p>
                <h3 class="text-2xl font-extrabold text-accent-600 dark:text-accent-400 mt-1">{{ $currency(summary.total_revenue) }}</h3>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <input v-model="startDate" type="date" class="form-input text-xs w-36" />
                <span class="self-center text-xs text-surface-400">s/d</span>
                <input v-model="endDate" type="date" class="form-input text-xs w-36" />
                <select v-model="locationId" class="form-select text-xs w-48">
                    <option value="">-- Semua Toko --</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
                <button @click="handleFilter" class="btn-secondary btn-sm">Filter</button>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Tgl Sold</th>
                            <th>Toko</th>
                            <th>Pelanggan</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in sales.data" :key="s.id">
                            <td class="font-mono font-bold text-xs">{{ s.code }}</td>
                            <td class="text-xs">{{ $date(s.sold_at) }}</td>
                            <td class="text-xs">{{ s.location?.name }}</td>
                            <td class="text-xs font-semibold">{{ s.customer_name }}</td>
                            <td class="text-right font-extrabold text-accent-600">{{ $currency(s.total_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="sales.links" />
        </div>
    </AuthenticatedLayout>
</template>
