<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const recordType = ref(props.filters.record_type || '');

const handleFilter = () => {
    router.get(
        route('sales-records.index'),
        { search: search.value, record_type: recordType.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pencatatan Penjualan" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Pencatatan Penjualan (Sales Record)</h1>
                <p class="text-xs text-surface-500 mt-1">Pencatatan transaksi ritel, grosir, atau rekap harian toko.</p>
            </div>
            <Link :href="route('sales-records.create')" class="btn-success">
                <i class="fa-solid fa-cart-plus"></i> Catat Penjualan Baru
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari no. dokumen / nama pembeli..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="recordType" @change="handleFilter" class="form-select text-xs w-48">
                    <option value="">-- Semua Tipe Transaksi --</option>
                    <option value="individual">Penjualan Individu / Nota Ritel</option>
                    <option value="daily_recap">Rekapitulasi Penjualan Harian</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tipe Record</th>
                            <th>Nama Pelanggan</th>
                            <th>Lokasi Penjualan</th>
                            <th>Tanggal Sold</th>
                            <th class="text-right">Total Transaksi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in sales.data" :key="s.id">
                            <td class="font-mono font-bold text-primary-600">
                                <Link :href="route('sales-records.show', s.id)" class="hover:underline">
                                    {{ s.code }}
                                </Link>
                            </td>
                            <td>
                                <span class="badge" :class="s.record_type === 'individual' ? 'badge-info' : 'badge-warning'">
                                    {{ s.record_type === 'individual' ? 'Ritel / Nota' : 'Rekap Harian' }}
                                </span>
                            </td>
                            <td class="text-xs font-semibold text-surface-900 dark:text-white">{{ s.customer_name }}</td>
                            <td>
                                <span class="badge badge-neutral">{{ s.location?.name }}</span>
                            </td>
                            <td class="text-xs">{{ $date(s.sold_at) }}</td>
                            <td class="text-right font-extrabold text-accent-600 dark:text-accent-400">
                                {{ $currency(s.total_amount) }}
                            </td>
                            <td class="text-right">
                                <Link :href="route('sales-records.show', s.id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Struk / Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="7" class="text-center py-8 text-surface-400 text-xs">Belum ada data pencatatan penjualan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="sales.links" />
        </div>
    </AuthenticatedLayout>
</template>
