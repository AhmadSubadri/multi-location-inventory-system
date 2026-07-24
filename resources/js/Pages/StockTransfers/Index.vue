<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transfers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const handleFilter = () => {
    router.get(
        route('stock-transfers.index'),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer Stok" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Transfer Stok (Gudang & Toko)</h1>
                <p class="text-xs text-surface-500 mt-1">Pengiriman stok antar gudang utama ke toko atau antar toko cabang.</p>
            </div>
            <Link :href="route('stock-transfers.create')" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Ajukan Transfer Stok
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari kode (TRF-XXXX)..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="status" @change="handleFilter" class="form-select text-xs w-48">
                    <option value="">-- Semua Status --</option>
                    <option value="submitted">Submitted (Pengajuan)</option>
                    <option value="approved">Approved (Disetujui)</option>
                    <option value="shipped">Shipped (In-Transit)</option>
                    <option value="received">Received (Selesai)</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Transfer</th>
                            <th>Asal Pengiriman</th>
                            <th>Tujuan Penerimaan</th>
                            <th>Pengaju</th>
                            <th>Status Workflow</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in transfers.data" :key="t.id">
                            <td class="font-mono font-bold text-primary-600">
                                <Link :href="route('stock-transfers.show', t.id)" class="hover:underline">
                                    {{ t.code }}
                                </Link>
                            </td>
                            <td class="text-xs font-semibold">
                                <span class="badge badge-neutral">{{ t.from_location?.name }}</span>
                            </td>
                            <td class="text-xs font-semibold">
                                <span class="badge badge-info">{{ t.to_location?.name }}</span>
                            </td>
                            <td class="text-xs text-surface-500">{{ t.requester?.name }}</td>
                            <td>
                                <span
                                    class="badge capitalize font-semibold"
                                    :class="{
                                        'badge-warning': t.status === 'submitted',
                                        'badge-info': t.status === 'approved',
                                        'badge-danger': t.status === 'shipped', // In transit
                                        'badge-success': t.status === 'received',
                                    }"
                                >
                                    <i class="fa-solid mr-1 text-[10px]" :class="{
                                        'fa-clock': t.status === 'submitted',
                                        'fa-circle-check': t.status === 'approved',
                                        'fa-truck-fast': t.status === 'shipped',
                                        'fa-box-open': t.status === 'received',
                                    }"></i>
                                    {{ t.status === 'shipped' ? 'Shipped (In-Transit)' : t.status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('stock-transfers.show', t.id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Detail Workflow
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="transfers.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Belum ada dokumen transfer stok.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="transfers.links" />
        </div>
    </AuthenticatedLayout>
</template>
