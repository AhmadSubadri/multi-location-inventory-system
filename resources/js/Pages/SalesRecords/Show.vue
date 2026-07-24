<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object,
});

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Nota Penjualan: ${sale.code}`" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
            <div>
                <Link :href="route('sales-records.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Penjualan
                </Link>
                <h1 class="text-2xl font-extrabold text-surface-900 dark:text-white">Nota Penjualan {{ sale.code }}</h1>
            </div>

            <button @click="printInvoice" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Struk / Nota
            </button>
        </div>

        <!-- Printable Receipt / Invoice Card -->
        <div class="max-w-2xl mx-auto card p-8 space-y-6 print:shadow-none print:border-none print:p-0">
            <!-- Header Store Identity -->
            <div class="text-center border-b border-surface-200 dark:border-surface-700 pb-4">
                <h2 class="text-xl font-extrabold text-surface-900 dark:text-white uppercase tracking-wider">
                    {{ sale.location?.name || 'PT AGRO SARANA TANI' }}
                </h2>
                <p class="text-xs text-surface-500 mt-0.5">{{ sale.location?.address }}</p>
                <p class="text-xs text-surface-500">Telp: {{ sale.location?.phone }}</p>
            </div>

            <!-- Receipt Meta -->
            <div class="grid grid-cols-2 gap-4 text-xs border-b border-surface-200 dark:border-surface-700 pb-4">
                <div>
                    <p><span class="text-surface-500">No. Transaksi:</span> <strong class="font-mono">{{ sale.code }}</strong></p>
                    <p><span class="text-surface-500">Pelanggan:</span> <strong>{{ sale.customer_name }}</strong></p>
                </div>
                <div class="text-right">
                    <p><span class="text-surface-500">Tanggal:</span> <strong>{{ $datetime(sale.sold_at) }}</strong></p>
                    <p><span class="text-surface-500">Kasir / User:</span> <strong>{{ sale.creator?.name }}</strong></p>
                </div>
            </div>

            <!-- Itemized Table -->
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-surface-200 dark:border-surface-700 font-bold uppercase text-surface-500">
                        <th class="py-2">Item Produk</th>
                        <th class="py-2 text-right">Qty</th>
                        <th class="py-2 text-right">Harga</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    <tr v-for="item in sale.items" :key="item.id">
                        <td class="py-2.5">
                            <span class="font-bold text-surface-900 dark:text-white">{{ item.product?.name }}</span>
                            <span class="block text-[10px] font-mono text-surface-400">Batch: {{ item.batch?.batch_number }}</span>
                        </td>
                        <td class="py-2.5 text-right font-semibold">{{ $number(item.qty) }} {{ item.unit?.symbol }}</td>
                        <td class="py-2.5 text-right">{{ $currency(item.unit_price) }}</td>
                        <td class="py-2.5 text-right font-bold text-surface-900 dark:text-white">{{ $currency(item.subtotal) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Grand Totals -->
            <div class="border-t-2 border-surface-900 dark:border-surface-100 pt-4 space-y-1.5 text-xs">
                <div class="flex justify-between text-surface-500">
                    <span>Subtotal:</span>
                    <span>{{ $currency(sale.subtotal) }}</span>
                </div>
                <div v-if="sale.discount_amount > 0" class="flex justify-between text-danger-600">
                    <span>Diskon:</span>
                    <span>-{{ $currency(sale.discount_amount) }}</span>
                </div>
                <div class="flex justify-between font-extrabold text-base text-surface-900 dark:text-white pt-2 border-t border-surface-200">
                    <span>TOTAL BAYAR:</span>
                    <span class="text-accent-600 dark:text-accent-400">{{ $currency(sale.total_amount) }}</span>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="text-center text-[11px] text-surface-400 pt-4 border-t border-dashed border-surface-200">
                <p>Terima kasih telah berbelanja di toko kami.</p>
                <p>Barang yang sudah dibeli hanya dapat diretur sesuai ketentuan yang berlaku.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
