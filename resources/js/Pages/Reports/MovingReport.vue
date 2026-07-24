<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    items: Array,
    filters: Object,
});

const months = ref(props.filters.months || 3);

const handleFilter = () => {
    router.get(route('reports.moving'), { months: months.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Fast & Slow Moving" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Laporan Analisis Fast & Slow Moving Products</h1>
                <p class="text-xs text-surface-500 mt-1">Peringkat kecepatan penjualan produk untuk perencanaan pengadaan / purchasing.</p>
            </div>
            <button @click="() => window.print()" class="btn-primary"><i class="fa-solid fa-print"></i> Cetak</button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b bg-surface-50/50 flex items-center gap-3">
                <label class="text-xs font-semibold">Rentang Evaluasi:</label>
                <select v-model="months" @change="handleFilter" class="form-select text-xs w-48">
                    <option :value="1">1 Bulan Terakhir</option>
                    <option :value="3">3 Bulan Terakhir</option>
                    <option :value="6">6 Bulan Terakhir</option>
                    <option :value="12">12 Bulan Terakhir</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>SKU & Nama Produk</th>
                            <th class="text-right">Total Terjual</th>
                            <th>Kategori Kategori Moving</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, idx) in items" :key="item.id">
                            <td class="font-bold text-xs">{{ idx + 1 }}</td>
                            <td>
                                <span class="font-bold text-surface-900 dark:text-white">{{ item.name }}</span>
                                <span class="block text-[10px] font-mono text-surface-400">SKU: {{ item.sku }}</span>
                            </td>
                            <td class="text-right font-extrabold text-primary-600">
                                {{ $number(item.total_sold) }} {{ item.unit }}
                            </td>
                            <td>
                                <span v-if="idx < 5 && item.total_sold > 0" class="badge badge-success">
                                    <i class="fa-solid fa-fire mr-1 text-[10px]"></i> FAST MOVING
                                </span>
                                <span v-else-if="item.total_sold > 0" class="badge badge-info">
                                    MEDIUM MOVING
                                </span>
                                <span v-else class="badge badge-danger">
                                    SLOW / NON MOVING
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
