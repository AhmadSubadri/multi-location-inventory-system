<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ledgers: Object,
    locations: Array,
    products: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const locationId = ref(props.filters.location_id || '');
const productId = ref(props.filters.product_id || '');
const type = ref(props.filters.type || '');

const handleFilter = () => {
    router.get(
        route('stock-ledgers.index'),
        {
            search: search.value,
            location_id: locationId.value,
            product_id: productId.value,
            type: type.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilter = () => {
    search.value = '';
    locationId.value = '';
    productId.value = '';
    type.value = '';
    handleFilter();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buku Besar Pergerakan Stok" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Stock Ledger (Buku Besar Pergerakan Stok)</h1>
                <p class="text-xs text-surface-500 mt-1">Audit trail terpusat dan mutasi stok barang (Single Source of Truth PRD §9 & §15).</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="SKU, Produk, atau Catatan..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>

                <select v-model="locationId" @change="handleFilter" class="form-select text-xs">
                    <option value="">-- Semua Lokasi --</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>

                <select v-model="productId" @change="handleFilter" class="form-select text-xs">
                    <option value="">-- Semua Produk --</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <select v-model="type" @change="handleFilter" class="form-select text-xs">
                    <option value="">-- Semua Tipe Pergerakan --</option>
                    <option value="IN">Penerimaan Supplier (IN)</option>
                    <option value="OUT_SALE">Penjualan (OUT_SALE)</option>
                    <option value="TRANSFER_IN">Transfer Masuk (TRANSFER_IN)</option>
                    <option value="TRANSFER_OUT">Transfer Keluar (TRANSFER_OUT)</option>
                    <option value="RETURN_IN">Retur Masuk (RETURN_IN)</option>
                    <option value="RETURN_OUT">Retur Keluar (RETURN_OUT)</option>
                    <option value="ADJUSTMENT">Stok Opname (ADJUSTMENT)</option>
                </select>

                <div class="flex items-center gap-2">
                    <button @click="handleFilter" class="btn-secondary btn-sm flex-1">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <button @click="resetFilter" class="btn-secondary btn-sm text-surface-500">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <!-- Ledger Audit Trail Table -->
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu (WIB)</th>
                            <th>Produk & Batch</th>
                            <th>Lokasi</th>
                            <th>Tipe Mutasi</th>
                            <th class="text-right">Stok Awal</th>
                            <th class="text-right">Perubahan (Qty)</th>
                            <th class="text-right">Stok Akhir</th>
                            <th>Petugas / User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="l in ledgers.data" :key="l.id">
                            <td class="text-xs font-mono whitespace-nowrap text-surface-500">
                                {{ $datetime(l.created_at) }}
                            </td>
                            <td>
                                <div class="font-bold text-xs text-surface-900 dark:text-white">{{ l.product?.name }}</div>
                                <div class="flex items-center gap-2 text-[10px] text-surface-400">
                                    <span class="font-mono bg-surface-100 dark:bg-surface-800 px-1 rounded">SKU: {{ l.product?.sku }}</span>
                                    <span v-if="l.batch" class="font-mono">Batch: {{ l.batch?.batch_number }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-neutral text-[10px]">{{ l.location?.name }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge font-mono text-[10px]"
                                    :class="{
                                        'badge-success': ['IN', 'TRANSFER_IN', 'RETURN_IN'].includes(l.type) || (l.type === 'ADJUSTMENT' && l.qty > 0),
                                        'badge-danger': ['OUT_SALE', 'TRANSFER_OUT', 'RETURN_OUT'].includes(l.type) || (l.type === 'ADJUSTMENT' && l.qty < 0),
                                    }"
                                >
                                    {{ l.type }}
                                </span>
                            </td>
                            <td class="text-right text-xs font-medium text-surface-500">
                                {{ $number(l.balance_before) }}
                            </td>
                            <td class="text-right font-extrabold text-xs" :class="l.qty > 0 ? 'text-accent-600 dark:text-accent-400' : 'text-danger-600 dark:text-danger-400'">
                                {{ l.qty > 0 ? `+${$number(l.qty)}` : $number(l.qty) }} {{ l.product?.base_unit?.symbol }}
                            </td>
                            <td class="text-right font-extrabold text-xs text-surface-900 dark:text-white">
                                {{ $number(l.balance_after) }}
                            </td>
                            <td class="text-xs text-surface-500">{{ l.creator?.name }}</td>
                        </tr>
                        <tr v-if="ledgers.data.length === 0">
                            <td colspan="8" class="text-center py-10 text-surface-400 text-xs">Belum ada catatan mutasi stok pada buku besar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="ledgers.links" />
        </div>
    </AuthenticatedLayout>
</template>
