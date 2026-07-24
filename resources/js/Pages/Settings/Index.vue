<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    doc_number_reset_mode: props.settings?.doc_number_reset_mode || 'monthly',
    prefix_goods_receipt: props.settings?.prefix_goods_receipt || 'GR',
    prefix_stock_transfer: props.settings?.prefix_stock_transfer || 'TRF',
    prefix_sales_record: props.settings?.prefix_sales_record || 'SLS',
    prefix_return: props.settings?.prefix_return || 'RTN',
    prefix_stock_opname: props.settings?.prefix_stock_opname || 'ADJ',
    enable_store_to_store_transfer: props.settings?.enable_store_to_store_transfer === '1',
    expiry_warning_days: parseInt(props.settings?.expiry_warning_days || '30'),
    tax_included_in_price: props.settings?.tax_included_in_price === '1',
});

const submit = () => {
    form.post(route('settings.update'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Konfigurasi Sistem" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Konfigurasi & Pengaturan Sistem</h1>
            <p class="text-xs text-surface-500 mt-1">Pengaturan penomoran dokumen, toggle transfer toko-ke-toko, dan notifikasi kadaluarsa.</p>
        </div>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <!-- Document Numbering Settings -->
                <div class="space-y-4">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-hashtags text-primary-600"></i> Formatter & Counter Nomor Dokumen
                    </h3>

                    <div>
                        <label class="form-label">Mode Reset Counter Nomor Dokumen</label>
                        <select v-model="form.doc_number_reset_mode" class="form-select">
                            <option value="monthly">Bulanan (Contoh: GR-2026/07/0001 — Reset tiap awal bulan)</option>
                            <option value="yearly">Tahunan (Contoh: GR-2026/0001 — Reset tiap awal tahun)</option>
                        </select>
                        <p class="text-[11px] text-surface-400 mt-1">Nomor dokumen di-generate secara otomatis dan sequential sesuai format pilihan Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Prefix Penerimaan Barang</label>
                            <input v-model="form.prefix_goods_receipt" type="text" class="form-input font-mono uppercase" />
                        </div>
                        <div>
                            <label class="form-label">Prefix Transfer Stok</label>
                            <input v-model="form.prefix_stock_transfer" type="text" class="form-input font-mono uppercase" />
                        </div>
                        <div>
                            <label class="form-label">Prefix Penjualan</label>
                            <input v-model="form.prefix_sales_record" type="text" class="form-input font-mono uppercase" />
                        </div>
                        <div>
                            <label class="form-label">Prefix Retur Dokumen</label>
                            <input v-model="form.prefix_return" type="text" class="form-input font-mono uppercase" />
                        </div>
                        <div>
                            <label class="form-label">Prefix Stok Opname</label>
                            <input v-model="form.prefix_stock_opname" type="text" class="form-input font-mono uppercase" />
                        </div>
                    </div>
                </div>

                <!-- Feature Toggle: Store to Store Transfer -->
                <div class="space-y-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-route text-accent-600"></i> Aturan Alur Transfer Stok (Feature Toggle)
                    </h3>

                    <div class="p-4 rounded-xl bg-surface-50 dark:bg-surface-800/40 border border-surface-200 dark:border-surface-700 space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.enable_store_to_store_transfer" class="rounded text-primary-600 w-4 h-4" />
                            <span class="font-bold text-xs text-surface-900 dark:text-white">Izinkan Transfer Langsung Toko-ke-Toko (Store to Store)</span>
                        </label>
                        <p class="text-xs text-surface-500 pl-7">
                            Jika diaktifkan (ON), toko cabang dapat mengirim stok langsung ke toko lain tanpa harus dikembalikan terlebih dahulu ke Gudang Utama.
                        </p>
                    </div>
                </div>

                <!-- Expiry Threshold & Tax -->
                <div class="space-y-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-bell text-warning-500"></i> Peringatan & Pajak Transaksi
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Peringatan Kadaluarsa (Hari)</label>
                            <input v-model.number="form.expiry_warning_days" type="number" min="1" max="365" class="form-input" />
                            <p class="text-[11px] text-surface-400 mt-1">Batch produk yang kadaluarsa dalam rentang hari ini akan muncul di dashboard alert.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                        <span>Simpan Konfigurasi</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
