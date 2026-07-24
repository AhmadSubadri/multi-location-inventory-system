<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
});

const totalStock = computed(() => {
    return props.product.batches?.reduce((acc, b) => acc + parseFloat(b.remaining_qty), 0) || 0;
});

import { computed } from 'vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Produk: ${product.name}`" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('products.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Produk
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">{{ product.name }}</h1>
                    <span v-if="product.is_hazardous" class="badge badge-danger">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px]"></i> B3 / Berbahaya
                    </span>
                </div>
            </div>
            <Link :href="route('products.edit', product.id)" class="btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit Produk
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Info & Batches -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overview Card -->
                <div class="card p-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs font-medium text-surface-500">Kode SKU</p>
                        <p class="text-sm font-bold font-mono text-surface-900 dark:text-white mt-0.5">{{ product.sku }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-surface-500">Kategori</p>
                        <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 mt-0.5">{{ product.category?.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-surface-500">Satuan Dasar</p>
                        <p class="text-sm font-semibold text-surface-900 dark:text-white mt-0.5">{{ product.base_unit?.name }} ({{ product.base_unit?.symbol }})</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-surface-500">Total Stok Keseluruhan</p>
                        <p class="text-base font-extrabold text-accent-600 dark:text-accent-400 mt-0.5">{{ $number(totalStock) }} {{ product.base_unit?.symbol }}</p>
                    </div>
                </div>

                <!-- Agricultural Specific Details -->
                <div class="card p-6 space-y-3">
                    <h3 class="font-bold text-sm text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-2">Spesifikasi Agricultural</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div><span class="text-surface-500">Merk / Produsen:</span> <strong class="text-surface-900 dark:text-white ml-1">{{ product.brand || '-' }}</strong></div>
                        <div><span class="text-surface-500">Bahan Aktif:</span> <strong class="text-surface-900 dark:text-white ml-1">{{ product.active_ingredient || '-' }}</strong></div>
                        <div><span class="text-surface-500">No. Registrasi Kementan:</span> <strong class="text-surface-900 dark:text-white ml-1">{{ product.registration_number || '-' }}</strong></div>
                        <div v-if="product.is_hazardous" class="sm:col-span-2 text-danger-600 bg-danger-50 dark:bg-danger-950/30 p-2.5 rounded-lg border border-danger-200">
                            <strong>Instruksi APD/B3:</strong> {{ product.hazardous_notes || 'Gunakan APD lengkap saat penggunaan' }}
                        </div>
                    </div>
                </div>

                <!-- Batch & Expiry Breakdown (FEFO Tracking) -->
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                        <h3 class="font-bold text-sm text-surface-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-boxes-packing text-primary-600"></i> Rincian Stok Batch & Kadaluarsa (FEFO)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nomor Batch</th>
                                    <th>Lokasi / Toko</th>
                                    <th>Tgl Kadaluarsa</th>
                                    <th class="text-right">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in product.batches" :key="b.id">
                                    <td class="font-mono font-semibold text-xs">{{ b.batch_number }}</td>
                                    <td>
                                        <span class="badge" :class="b.location?.type === 'warehouse' ? 'badge-warning' : 'badge-info'">
                                            {{ b.location?.name }}
                                        </span>
                                    </td>
                                    <td class="text-xs font-medium">
                                        <span v-if="!b.expiry_date" class="text-surface-400">Non-expiring</span>
                                        <span v-else-if="new Date(b.expiry_date) < new Date()" class="text-danger-600 font-bold bg-danger-50 px-2 py-0.5 rounded">
                                            EXPIRED ({{ $date(b.expiry_date) }})
                                        </span>
                                        <span v-else class="text-surface-700 dark:text-surface-300">
                                            {{ $date(b.expiry_date) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-surface-900 dark:text-white">
                                        {{ $number(b.remaining_qty) }} {{ product.base_unit?.symbol }}
                                    </td>
                                </tr>
                                <tr v-if="!product.batches || product.batches.length === 0">
                                    <td colspan="4" class="text-center py-6 text-surface-400 text-xs">Belum ada batch stok tersimpan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Pricing & Conversions -->
            <div class="space-y-6">
                <!-- Multi-tier Prices Card -->
                <div class="card p-6 space-y-4">
                    <h3 class="font-bold text-sm text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-accent-600"></i> Daftar Harga (Multi-Tier)
                    </h3>
                    <div class="space-y-2">
                        <div v-for="pr in product.prices" :key="pr.id" class="flex items-center justify-between p-2.5 rounded-lg bg-surface-50 dark:bg-surface-800 text-xs">
                            <div>
                                <span class="capitalize font-bold text-surface-900 dark:text-white">{{ pr.price_type }}</span>
                                <span class="text-surface-400 ml-1">(Min {{ pr.min_qty }} {{ product.base_unit?.symbol }})</span>
                            </div>
                            <span class="font-extrabold text-accent-600 dark:text-accent-400">{{ $currency(pr.price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Unit Conversions Card -->
                <div class="card p-6 space-y-4">
                    <h3 class="font-bold text-sm text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-primary-600"></i> Konversi Satuan
                    </h3>
                    <div v-if="product.unit_conversions?.length > 0" class="space-y-2">
                        <div v-for="uc in product.unit_conversions" :key="uc.id" class="p-2.5 rounded-lg bg-surface-50 dark:bg-surface-800 text-xs flex items-center justify-between font-medium">
                            <span>1 {{ uc.from_unit?.name }}</span>
                            <span class="text-surface-400">=</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ uc.conversion_factor }} {{ product.base_unit?.name }}</span>
                        </div>
                    </div>
                    <p v-else class="text-xs text-surface-400 italic">Tidak ada konversi tambahan.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
