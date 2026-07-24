<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    opname: Object,
});

const approve = () => {
    if (confirm(`Apakah Anda yakin ingin menyetujui stok opname ${props.opname.code}? Penyesuaian selisih stok akan langsung diterapkan ke buku besar (Stock Ledger)!`)) {
        router.post(route('stock-opnames.approve', props.opname.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Opname: ${opname.code}`" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="route('stock-opnames.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Opname
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">{{ opname.code }}</h1>
                    <span class="badge" :class="opname.status === 'approved' ? 'badge-success' : 'badge-warning'">
                        {{ opname.status === 'approved' ? 'Disetujui / Ledger Adjusted' : 'Draft (Menunggu Approval)' }}
                    </span>
                </div>
            </div>

            <div v-if="opname.status === 'draft'">
                <button @click="approve" class="btn-success">
                    <i class="fa-solid fa-circle-check"></i> Setujui & Terapkan Penyesuaian Stok
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-3 card p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-surface-500 block">Lokasi Opname:</span>
                    <strong class="text-sm font-bold text-primary-600">{{ opname.location?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Petugas Opname:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ opname.performer?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Disetujui Oleh:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ opname.approver?.name || '-' }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Waktu Dibuat:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ $datetime(opname.created_at) }}</strong>
                </div>
            </div>

            <div class="lg:col-span-3 card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 font-bold text-sm">
                    Tabel Perbandingan Stok Sistem vs Fisik Lapangan
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Nomor Batch</th>
                                <th class="text-right">Qty Sistem</th>
                                <th class="text-right">Hitung Fisik</th>
                                <th class="text-right">Selisih (Adjustment)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in opname.items" :key="item.id">
                                <td class="font-bold text-surface-900 dark:text-white">
                                    {{ item.product?.name }}
                                </td>
                                <td class="font-mono text-xs font-semibold">{{ item.batch?.batch_number }}</td>
                                <td class="text-right text-surface-500">{{ $number(item.system_qty) }} {{ item.product?.base_unit?.symbol }}</td>
                                <td class="text-right font-extrabold text-primary-600">{{ $number(item.physical_qty) }} {{ item.product?.base_unit?.symbol }}</td>
                                <td class="text-right font-extrabold" :class="item.difference < 0 ? 'text-danger-600' : (item.difference > 0 ? 'text-accent-600' : 'text-surface-400')">
                                    {{ item.difference > 0 ? `+${$number(item.difference)}` : $number(item.difference) }} {{ item.product?.base_unit?.symbol }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
