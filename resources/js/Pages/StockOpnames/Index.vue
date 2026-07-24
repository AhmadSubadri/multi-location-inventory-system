<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    opnames: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleFilter = () => {
    router.get(
        route('stock-opnames.index'),
        { search: search.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Stok Opname Fisik" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Stok Opname (Penyesuaian Fisik)</h1>
                <p class="text-xs text-surface-500 mt-1">Perhitungan fisik persediaan gudang/toko dan penyesuaian selisih stok.</p>
            </div>
            <Link :href="route('stock-opnames.create')" class="btn-primary">
                <i class="fa-solid fa-clipboard-check"></i> Buat Stok Opname Baru
            </Link>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-xs relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari kode opname (ADJ-XXXX)..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Opname</th>
                            <th>Lokasi Fisik</th>
                            <th>Petugas Opname</th>
                            <th>Status</th>
                            <th>Persetujuan (Approver)</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in opnames.data" :key="o.id">
                            <td class="font-mono font-bold text-primary-600">
                                <Link :href="route('stock-opnames.show', o.id)" class="hover:underline">
                                    {{ o.code }}
                                </Link>
                            </td>
                            <td>
                                <span class="badge badge-info font-semibold">{{ o.location?.name }}</span>
                            </td>
                            <td class="text-xs">{{ o.performer?.name }}</td>
                            <td>
                                <span class="badge" :class="o.status === 'approved' ? 'badge-success' : 'badge-warning'">
                                    {{ o.status === 'approved' ? 'Disetujui (Ledger Adjusted)' : 'Draft (Menunggu Approval)' }}
                                </span>
                            </td>
                            <td class="text-xs text-surface-500">{{ o.approver?.name || '-' }}</td>
                            <td class="text-right">
                                <Link :href="route('stock-opnames.show', o.id)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Detail Opname
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="opnames.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Belum ada dokumen stok opname.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="opnames.links" />
        </div>
    </AuthenticatedLayout>
</template>
