<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    receipts: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const handleFilter = () => {
    router.get(
        route('goods-receipts.index'),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Penerimaan Barang" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Penerimaan Barang (Goods Receipt)</h1>
                <p class="text-xs text-surface-500 mt-1">Pencatatan barang masuk dari supplier ke gudang utama / toko.</p>
            </div>
            <Link :href="route('goods-receipts.create')" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Terima Barang Baru
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari nomor dokumen (GR-XXXX)..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="status" @change="handleFilter" class="form-select text-xs w-44">
                    <option value="">-- Semua Status --</option>
                    <option value="draft">Draft (Belum Approve)</option>
                    <option value="received">Received (Stok Masuk)</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Dokumen</th>
                            <th>Supplier / Pemasok</th>
                            <th>Lokasi Tujuan</th>
                            <th>Tgl Diterima</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in receipts.data" :key="r.id">
                            <td class="font-mono font-bold text-primary-600">
                                <Link :href="route('goods-receipts.show', r.id)" class="hover:underline">
                                    {{ r.code }}
                                </Link>
                            </td>
                            <td class="font-semibold text-xs text-surface-900 dark:text-white">{{ r.supplier?.name }}</td>
                            <td>
                                <span class="badge" :class="r.location?.type === 'warehouse' ? 'badge-warning' : 'badge-info'">
                                    {{ r.location?.name }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $date(r.received_at) }}</td>
                            <td class="text-xs text-surface-500">{{ r.creator?.name }}</td>
                            <td>
                                <span class="badge" :class="r.status === 'received' ? 'badge-success' : 'badge-warning'">
                                    <i class="fa-solid mr-1 text-[10px]" :class="r.status === 'received' ? 'fa-circle-check' : 'fa-clock'"></i>
                                    {{ r.status === 'received' ? 'Selesai / Stok Masuk' : 'Draft' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link :href="route('goods-receipts.show', r.id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="receipts.data.length === 0">
                            <td colspan="7" class="text-center py-8 text-surface-400 text-xs">Belum ada dokumen penerimaan barang.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="receipts.links" />
        </div>
    </AuthenticatedLayout>
</template>
