<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    returnDoc: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Retur: ${returnDoc.code}`" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="route('returns.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Retur
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">{{ returnDoc.code }}</h1>
                    <span class="badge badge-success">Diproses & Disetujui</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-3 card p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-surface-500 block">Skenario Retur:</span>
                    <strong class="text-sm font-bold text-surface-900 dark:text-white uppercase">{{ returnDoc.type?.replace('_', ' ') }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Lokasi Asal:</span>
                    <strong class="text-sm font-bold text-primary-600">{{ returnDoc.location?.name }}</strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Pihak / Lokasi Tujuan:</span>
                    <strong class="text-sm font-bold text-primary-600">
                        {{ returnDoc.supplier?.name || returnDoc.relatedLocation?.name || 'Ritel Consumen' }}
                    </strong>
                </div>
                <div>
                    <span class="text-surface-500 block">Pembuat Dokumen:</span>
                    <strong class="text-sm text-surface-900 dark:text-white">{{ returnDoc.creator?.name }}</strong>
                </div>
                <div class="sm:col-span-4 bg-warning-50 p-2.5 rounded-lg border border-warning-200">
                    <strong>Alasan Retur:</strong> {{ returnDoc.reason }}
                </div>
            </div>

            <div class="lg:col-span-3 card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 font-bold text-sm">
                    Rincian Barang Yang Diretur
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Nomor Batch</th>
                                <th>Kondisi Fisik</th>
                                <th class="text-right">Qty Diretur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in returnDoc.items" :key="item.id">
                                <td class="font-bold text-surface-900 dark:text-white">
                                    {{ item.product?.name }}
                                </td>
                                <td class="font-mono text-xs font-semibold">{{ item.batch?.batch_number }}</td>
                                <td>
                                    <span class="badge" :class="item.condition === 'good' ? 'badge-success' : 'badge-danger'">
                                        {{ item.condition === 'good' ? 'Kondisi Baik' : 'Rusak / Defect' }}
                                    </span>
                                </td>
                                <td class="text-right font-bold text-danger-600">{{ $number(item.qty) }} {{ item.product?.base_unit?.symbol }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
