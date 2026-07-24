<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    movements: Object,
    locations: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const locationId = ref(props.filters.location_id || '');

const handleFilter = () => {
    router.get(
        route('reports.ledger'),
        { start_date: startDate.value, end_date: endDate.value, location_id: locationId.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Pergerakan Stok" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Rekapitulasi Pergerakan Stok</h1>
                <p class="text-xs text-surface-500 mt-1">Rekap seluruh mutasi masuk/keluar pada periode tertentu.</p>
            </div>
            <button @click="() => window.print()" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <input v-model="startDate" type="date" class="form-input text-xs w-36" />
                <span class="self-center text-xs text-surface-400">s/d</span>
                <input v-model="endDate" type="date" class="form-input text-xs w-36" />
                <select v-model="locationId" class="form-select text-xs w-48">
                    <option value="">-- Semua Lokasi --</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
                <button @click="handleFilter" class="btn-secondary btn-sm">Filter Tanggal</button>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu (WIB)</th>
                            <th>Produk</th>
                            <th>Lokasi</th>
                            <th>Tipe</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Sisa Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in movements.data" :key="m.id">
                            <td class="text-xs font-mono">{{ $datetime(m.created_at) }}</td>
                            <td class="font-bold text-xs">{{ m.product?.name }}</td>
                            <td class="text-xs">{{ m.location?.name }}</td>
                            <td><span class="badge badge-info text-[10px]">{{ m.type }}</span></td>
                            <td class="text-right font-bold" :class="m.qty > 0 ? 'text-accent-600' : 'text-danger-600'">
                                {{ m.qty > 0 ? `+${$number(m.qty)}` : $number(m.qty) }} {{ m.product?.base_unit?.symbol }}
                            </td>
                            <td class="text-right font-extrabold">{{ $number(m.balance_after) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="movements.links" />
        </div>
    </AuthenticatedLayout>
</template>
