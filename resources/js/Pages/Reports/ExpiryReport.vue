<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    batches: Object,
    locations: Array,
    filters: Object,
});

const days = ref(props.filters.days || 30);
const locationId = ref(props.filters.location_id || '');

const handleFilter = () => {
    router.get(
        route('reports.expiry'),
        { days: days.value, location_id: locationId.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Kadaluarsa Batch" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Kadaluarsa Produk (FEFO Expiry Alert)</h1>
                <p class="text-xs text-surface-500 mt-1">Monitoring batch produk pesticide/bibit yang akan atau sudah kadaluarsa.</p>
            </div>
            <button @click="() => window.print()" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <select v-model="days" @change="handleFilter" class="form-select text-xs w-48">
                    <option value="15">Peringatan 15 Hari</option>
                    <option value="30">Peringatan 30 Hari</option>
                    <option value="60">Peringatan 60 Hari</option>
                    <option value="90">Peringatan 90 Hari</option>
                </select>

                <select v-model="locationId" @change="handleFilter" class="form-select text-xs w-48">
                    <option value="">-- Semua Lokasi --</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nomor Batch</th>
                            <th>Produk</th>
                            <th>Lokasi</th>
                            <th>Tgl Kadaluarsa</th>
                            <th>Sisa Qty</th>
                            <th>Status Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in batches.data" :key="b.id">
                            <td class="font-mono font-bold text-xs">{{ b.batch_number }}</td>
                            <td class="font-bold text-xs">{{ b.product?.name }}</td>
                            <td class="text-xs">{{ b.location?.name }}</td>
                            <td class="text-xs font-semibold">{{ $date(b.expiry_date) }}</td>
                            <td class="text-xs font-extrabold text-right">{{ $number(b.remaining_qty) }} {{ b.product?.base_unit?.symbol }}</td>
                            <td>
                                <span v-if="new Date(b.expiry_date) < new Date()" class="badge badge-danger">
                                    EXPIRED (Kadaluarsa)
                                </span>
                                <span v-else class="badge badge-warning">
                                    Hampir Kadaluarsa
                                </span>
                            </td>
                        </tr>
                        <tr v-if="batches.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Tidak ada batch produk yang mendekati kadaluarsa.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="batches.links" />
        </div>
    </AuthenticatedLayout>
</template>
