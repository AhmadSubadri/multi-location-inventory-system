<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    receipts: Object,
    filters: Object,
});

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const handleFilter = () => {
    router.get(route('reports.goods-receipts'), { start_date: startDate.value, end_date: endDate.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Penerimaan Supplier" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Penerimaan Barang Supplier</h1>
            <button @click="() => window.print()" class="btn-primary"><i class="fa-solid fa-print"></i> Cetak</button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b bg-surface-50/50 flex gap-3">
                <input v-model="startDate" type="date" class="form-input text-xs w-36" />
                <span class="self-center text-xs">s/d</span>
                <input v-model="endDate" type="date" class="form-input text-xs w-36" />
                <button @click="handleFilter" class="btn-secondary btn-sm">Filter</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Supplier</th>
                        <th>Gudang Tujuan</th>
                        <th>Tgl Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in receipts.data" :key="r.id">
                        <td class="font-mono font-bold text-xs">{{ r.code }}</td>
                        <td class="text-xs font-semibold">{{ r.supplier?.name }}</td>
                        <td class="text-xs">{{ r.location?.name }}</td>
                        <td class="text-xs">{{ $date(r.received_at) }}</td>
                    </tr>
                </tbody>
            </table>
            <Pagination :links="receipts.links" />
        </div>
    </AuthenticatedLayout>
</template>
