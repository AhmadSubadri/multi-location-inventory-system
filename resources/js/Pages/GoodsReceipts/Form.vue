<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    suppliers: Array,
    locations: Array,
    products: Array,
    units: Array,
});

const form = useForm({
    supplier_id: props.suppliers?.[0]?.id || '',
    location_id: props.locations?.[0]?.id || '',
    received_at: new Date().toISOString().split('T')[0],
    notes: '',
    items: [
        {
            product_id: '',
            qty: 1,
            unit_id: '',
            unit_price: 0,
            batch_number: `B${new Date().getFullYear()}-${Math.floor(100 + Math.random() * 900)}`,
            production_date: '',
            expiry_date: '',
        },
    ],
});

const addItemRow = () => {
    form.items.push({
        product_id: '',
        qty: 1,
        unit_id: '',
        unit_price: 0,
        batch_number: `B${new Date().getFullYear()}-${Math.floor(100 + Math.random() * 900)}`,
        production_date: '',
        expiry_date: '',
    });
};

const removeItemRow = (idx) => {
    form.items.splice(idx, 1);
};

const onProductSelect = (idx) => {
    const pId = form.items[idx].product_id;
    const p = props.products.find((prod) => prod.id === pId);
    if (p) {
        form.items[idx].unit_id = p.base_unit_id;
    }
};

const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.qty || 0) * parseFloat(item.unit_price || 0)), 0);
});

const submit = () => {
    form.post(route('goods-receipts.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Form Penerimaan Barang Supplier" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('goods-receipts.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Penerimaan
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Penerimaan Barang Dari Supplier</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Document Header -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-primary-600"></i> Informasi Dokumen Penerimaan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Pemasok / Supplier <span class="text-danger-500">*</span></label>
                        <select v-model="form.supplier_id" required class="form-select">
                            <option value="">-- Pilih Supplier --</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Gudang / Lokasi Tujuan <span class="text-danger-500">*</span></label>
                        <select v-model="form.location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tanggal Penerimaan <span class="text-danger-500">*</span></label>
                        <input v-model="form.received_at" type="date" required class="form-input text-xs" />
                    </div>
                </div>

                <div>
                    <label class="form-label">Catatan / No. Surat Jalan Supplier</label>
                    <input v-model="form.notes" type="text" class="form-input" placeholder="contoh: No. SJ Supplier: SJ/2026/07/8891" />
                </div>
            </div>

            <!-- Items & Batches Table -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-accent-600"></i> Rincian Barang & Batch Kadaluarsa
                    </h3>
                    <button type="button" @click="addItemRow" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Item Baris
                    </button>
                </div>

                <div class="space-y-4">
                    <div v-for="(item, idx) in form.items" :key="idx" class="p-4 rounded-xl bg-surface-50 dark:bg-surface-800/40 border border-surface-200 dark:border-surface-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-primary-600">Item #{{ idx + 1 }}</span>
                            <button v-if="form.items.length > 1" type="button" @click="removeItemRow(idx)" class="btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <label class="form-label text-xs">Pilih Produk <span class="text-danger-500">*</span></label>
                                <select v-model="item.product_id" @change="onProductSelect(idx)" required class="form-select text-xs">
                                    <option value="">-- Pilih Produk --</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs">Jumlah (Qty) <span class="text-danger-500">*</span></label>
                                <input v-model.number="item.qty" type="number" min="0.01" step="any" required class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Satuan <span class="text-danger-500">*</span></label>
                                <select v-model="item.unit_id" required class="form-select text-xs">
                                    <option value="">-- Satuan --</option>
                                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol }})</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs">Harga Beli Satuan (Rp)</label>
                                <input v-model.number="item.unit_price" type="number" min="0" step="500" required class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Nomor Batch Baru <span class="text-danger-500">*</span></label>
                                <input v-model="item.batch_number" type="text" required class="form-input text-xs font-mono uppercase" placeholder="B2026-xxx" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Tgl Produksi</label>
                                <input v-model="item.production_date" type="date" class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Tgl Kadaluarsa (Expiry)</label>
                                <input v-model="item.expiry_date" type="date" class="form-input text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-surface-200 dark:border-surface-700">
                    <span class="text-sm font-bold text-surface-700 dark:text-surface-300">Total Nominal Pembelian:</span>
                    <span class="text-2xl font-extrabold text-accent-600 dark:text-accent-400">{{ $currency(grandTotal) }}</span>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('goods-receipts.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Simpan Sebagai Draft</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
