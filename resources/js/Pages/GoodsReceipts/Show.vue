<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    receipt: Object,
});

const approve = () => {
    if (confirm(`Apakah Anda yakin ingin menyetujui dokumen penerimaan ${props.receipt.code}? Stok akan langsung bertambah ke gudang!`)) {
        router.post(route('goods-receipts.approve', props.receipt.id));
    }
};

const deleteDraft = () => {
    if (confirm(`Hapus draft penerimaan ${props.receipt.code}?`)) {
        router.delete(route('goods-receipts.destroy', props.receipt.id));
    }
};

const grandTotal = computed(() => {
    return props.receipt.items?.reduce((sum, i) => sum + (parseFloat(i.qty) * parseFloat(i.unit_price)), 0) || 0;
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Penerimaan: ${receipt.code}`" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="route('goods-receipts.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Penerimaan
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">{{ receipt.code }}</h1>
                    <span class="badge" :class="receipt.status === 'received' ? 'badge-success' : 'badge-warning'">
                        {{ receipt.status === 'received' ? 'Received / Stok Masuk' : 'Draft (Belum Approved)' }}
                    </span>
                </div>
            </div>

            <!-- Workflow Approve Actions -->
            <div v-if="receipt.status === 'draft'" class="flex items-center gap-2">
                <button @click="deleteDraft" class="btn-danger btn-sm">
                    <i class="fa-solid fa-trash"></i> Hapus Draft
                </button>
                <button @click="approve" class="btn-success">
                    <i class="fa-solid fa-circle-check"></i> Setujui & Masukkan Stok
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Header Meta -->
            <div class="lg:col-span-3 card p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-surface-500 block">Supplier / Pemasok:</span>
                    <strong class="text-sm font-bold text-surface-900 dark:text-white">{{ receipt.supplier?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Lokasi Tujuan:</span>
                    <strong class="text-sm font-bold text-primary-600">{{ receipt.location?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Tgl Diterima:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ $date(receipt.received_at) }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Dibuat Oleh:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ receipt.creator?.name }}</strong>
                </div>
                <div v-if="receipt.notes" class="sm:col-span-4 bg-surface-50 p-2.5 rounded-lg border">
                    <strong>Catatan Dokumen:</strong> {{ receipt.notes }}
                </div>
            </div>

            <!-- Items Table -->
            <div class="lg:col-span-3 card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 font-bold text-sm">
                    Rincian Barang & Batch Diterima
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Nomor Batch</th>
                                <th>Tgl Kadaluarsa</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in receipt.items" :key="item.id">
                                <td class="font-bold text-surface-900 dark:text-white">
                                    {{ item.product?.name }}
                                    <span class="block text-[10px] font-mono text-surface-400">SKU: {{ item.product?.sku }}</span>
                                </td>
                                <td class="font-mono text-xs font-semibold">{{ item.batch_number }}</td>
                                <td class="text-xs">{{ item.expiry_date ? $date(item.expiry_date) : 'Non-expiring' }}</td>
                                <td class="text-right font-bold">{{ $number(item.qty) }} {{ item.unit?.symbol }}</td>
                                <td class="text-right">{{ $currency(item.unit_price) }}</td>
                                <td class="text-right font-extrabold text-accent-600">{{ $currency(item.qty * item.unit_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-700 flex justify-end items-center gap-3">
                    <span class="text-sm font-bold">Total Nilai Penerimaan:</span>
                    <span class="text-2xl font-extrabold text-accent-600">{{ $currency(grandTotal) }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
