<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activeLocation: Object,
    stats: Object,
    topProducts: Array,
    salesTrend: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard Overview" />

        <!-- Header Title Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-gauge-high"></i> Overview Real-time
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-surface-900 dark:text-white tracking-tight">
                    Dashboard {{ activeLocation?.name ? `— ${activeLocation.name}` : '' }}
                </h1>
                <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 mt-1">
                    Ringkasan performa penjualan, ketersediaan stok, dan status operasional terbaru.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <Link :href="route('goods-receipts.create')" class="btn-primary btn-sm shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                    <span>Terima Barang</span>
                </Link>
                <Link :href="route('stock-transfers.create')" class="btn-secondary btn-sm shadow-xs">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Transfer Stok</span>
                </Link>
                <Link :href="route('sales-records.create')" class="btn-success btn-sm shadow-xs">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Catat Penjualan</span>
                </Link>
            </div>
        </div>

        <!-- 4 Primary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <!-- Sales Today -->
            <div class="card p-5 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Penjualan Hari Ini</p>
                        <h3 class="text-2xl font-extrabold text-surface-900 dark:text-white mt-1">
                            {{ $currency(stats.salesToday) }}
                        </h3>
                        <p class="text-[11px] text-surface-400 mt-1">
                            Bulan ini: <span class="font-semibold text-surface-700 dark:text-surface-300">{{ $currency(stats.salesThisMonth) }}</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-accent-500/10 text-accent-600 dark:text-accent-400 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                </div>
            </div>

            <!-- Total Stock Items -->
            <div class="card p-5 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Total Item Produk</p>
                        <h3 class="text-2xl font-extrabold text-surface-900 dark:text-white mt-1">
                            {{ $number(stats.totalStockItems) }}
                        </h3>
                        <p class="text-[11px] text-surface-400 mt-1">
                            Dari {{ stats.totalProducts }} Master SKU
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>

            <!-- Low Stock Warning -->
            <div class="card p-5 relative overflow-hidden group hover:shadow-md transition-all border-l-4 border-l-warning-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Peringatan Stok Minimum</p>
                        <h3 class="text-2xl font-extrabold text-warning-600 dark:text-warning-400 mt-1">
                            {{ stats.lowStockCount }} <span class="text-xs font-normal text-surface-400">SKU</span>
                        </h3>
                        <p class="text-[11px] text-surface-400 mt-1">
                            Di bawah batas reorder
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-warning-500/10 text-warning-600 dark:text-warning-400 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon / Expired -->
            <div class="card p-5 relative overflow-hidden group hover:shadow-md transition-all border-l-4 border-l-danger-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Hampir / Kadaluarsa</p>
                        <h3 class="text-2xl font-extrabold text-danger-600 dark:text-danger-400 mt-1">
                            {{ stats.expiringSoonCount }} <span class="text-xs font-normal text-surface-400">Batch</span>
                        </h3>
                        <p class="text-[11px] text-danger-500 mt-1" v-if="stats.expiredCount > 0">
                            {{ stats.expiredCount }} batch sudah kadaluarsa!
                        </p>
                        <p class="text-[11px] text-surface-400 mt-1" v-else>
                            Peringatan 30 hari
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-danger-500/10 text-danger-600 dark:text-danger-400 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Workflows Banner -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="card p-4 flex items-center gap-4 bg-gradient-to-r from-primary-50 to-white dark:from-surface-900 dark:to-surface-800 border-primary-200">
                <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-surface-500 font-medium">Penerimaan Barang Draft</p>
                    <p class="text-lg font-bold text-surface-900 dark:text-white">{{ stats.pendingReceipts }} Dokumen</p>
                </div>
                <Link :href="route('goods-receipts.index')" class="text-xs text-primary-600 hover:underline font-semibold">
                    Lihat <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>

            <div class="card p-4 flex items-center gap-4 bg-gradient-to-r from-warning-50 to-white dark:from-surface-900 dark:to-surface-800 border-warning-200">
                <div class="w-10 h-10 rounded-xl bg-warning-500 text-white flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-dolly"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-surface-500 font-medium">Transfer Menunggu Approval</p>
                    <p class="text-lg font-bold text-surface-900 dark:text-white">{{ stats.pendingTransfers }} Transfer</p>
                </div>
                <Link :href="route('stock-transfers.index')" class="text-xs text-warning-600 hover:underline font-semibold">
                    Lihat <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>

            <div class="card p-4 flex items-center gap-4 bg-gradient-to-r from-accent-50 to-white dark:from-surface-900 dark:to-surface-800 border-accent-200">
                <div class="w-10 h-10 rounded-xl bg-accent-600 text-white flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-surface-500 font-medium">Transfer In-Transit (Shipped)</p>
                    <p class="text-lg font-bold text-surface-900 dark:text-white">{{ stats.inTransitTransfers }} Pengiriman</p>
                </div>
                <Link :href="route('stock-transfers.index')" class="text-xs text-accent-600 hover:underline font-semibold">
                    Terima <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>
        </div>

        <!-- Top Selling Products Table -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-surface-900 dark:text-white">Produk Terlaris Bulan Ini</h3>
                    <p class="text-xs text-surface-500">Top 5 item berdasarkan kuantitas terjuall</p>
                </div>
                <Link :href="route('reports.sales')" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                    Laporan Lengkap <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-right">Total Qty Terjual</th>
                            <th class="text-right">Total Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(p, idx) in topProducts" :key="idx">
                            <td class="font-semibold text-surface-900 dark:text-white flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-surface-100 dark:bg-surface-800 text-xs text-surface-600 dark:text-surface-400 font-bold flex items-center justify-center shrink-0">
                                    {{ idx + 1 }}
                                </span>
                                {{ p.name }}
                            </td>
                            <td class="text-right font-medium">
                                {{ $number(p.total_qty) }} {{ p.unit }}
                            </td>
                            <td class="text-right font-bold text-accent-600 dark:text-accent-400">
                                {{ $currency(p.total_revenue) }}
                            </td>
                        </tr>
                        <tr v-if="!topProducts || topProducts.length === 0">
                            <td colspan="3" class="text-center py-8 text-surface-400 text-xs">
                                Belum ada transaksi penjualan pada bulan ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
