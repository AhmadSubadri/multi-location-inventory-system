<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    returns: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');

const handleFilter = () => {
    router.get(
        route('returns.index'),
        { search: search.value, type: type.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Retur Barang" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Retur Barang (Returns)</h1>
                <p class="text-xs text-surface-500 mt-1">Pencatatan retur dari toko ke gudang, gudang ke supplier, atau konsumen ke toko.</p>
            </div>
            <Link :href="route('returns.create')" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Buat Dokumen Retur
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari kode (RTN-XXXX)..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="type" @change="handleFilter" class="form-select text-xs w-56">
                    <option value="">-- Semua Skenario Retur --</option>
                    <option value="store_to_warehouse">Retur Toko → Gudang Utama</option>
                    <option value="warehouse_to_supplier">Retur Gudang → Supplier Vendor</option>
                    <option value="customer_to_store">Retur Konsumen → Toko</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Retur</th>
                            <th>Tipe Skenario</th>
                            <th>Lokasi / Pihak Terkait</th>
                            <th>Alasan Retur</th>
                            <th>Pembuat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in returns.data" :key="r.id">
                            <td class="font-mono font-bold text-primary-600">
                                <Link :href="route('returns.show', r.id)" class="hover:underline">
                                    {{ r.code }}
                                </Link>
                            </td>
                            <td>
                                <span class="badge" :class="r.type === 'customer_to_store' ? 'badge-success' : 'badge-warning'">
                                    {{ r.type === 'store_to_warehouse' ? 'Toko → Gudang' : (r.type === 'warehouse_to_supplier' ? 'Gudang → Supplier' : 'Konsumen → Toko') }}
                                </span>
                            </td>
                            <td class="text-xs font-semibold">
                                <span v-if="r.supplier" class="text-surface-900 dark:text-white">{{ r.supplier.name }}</span>
                                <span v-else-if="r.relatedLocation" class="text-surface-900 dark:text-white">{{ r.relatedLocation.name }}</span>
                                <span v-else class="text-surface-400">Pembeli / Ritel</span>
                            </td>
                            <td class="text-xs text-surface-600 dark:text-surface-300 max-w-xs truncate">{{ r.reason }}</td>
                            <td class="text-xs text-surface-500">{{ r.creator?.name }}</td>
                            <td class="text-right">
                                <Link :href="route('returns.show', r.id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="returns.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Belum ada dokumen retur barang.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="returns.links" />
        </div>
    </AuthenticatedLayout>
</template>
