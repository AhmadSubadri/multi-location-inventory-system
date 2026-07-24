<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    locations: Array,
    products: Array,
    taxes: Array,
    discounts: Array,
    activeLocationId: Number,
});

const form = useForm({
    location_id: props.activeLocationId || props.locations?.[0]?.id || '',
    customer_name: 'Pelanggan Umum (Cash)',
    customer_contact: '',
    record_type: 'individual',
    sold_at: new Date().toISOString().split('T')[0],
    notes: '',
    reference_number: '',
    items: [
        {
            product_id: '',
            batch_id: '',
            qty: 1,
            unit_id: '',
            unit_price: 0,
            discount_amount: 0,
            tax_amount: 0,
        },
    ],
});

const availableBatches = (productId, locationId) => {
    if (!productId || !locationId) return [];
    const p = props.products.find((prod) => prod.id === productId);
    if (!p || !p.batches) return [];
    return p.batches.filter((b) => b.location_id === locationId && parseFloat(b.remaining_qty) > 0);
};

const onProductSelect = (idx) => {
    const pId = form.items[idx].product_id;
    const p = props.products.find((prod) => prod.id === pId);
    if (p) {
        form.items[idx].unit_id = p.base_unit_id;
        // Auto pick retail price
        const priceObj = p.prices?.find((pr) => pr.price_type === 'retail') || p.prices?.[0];
        form.items[idx].unit_price = priceObj ? priceObj.price : 0;

        // Auto pick FEFO batch
        const batches = availableBatches(pId, form.location_id);
        if (batches.length > 0) {
            form.items[idx].batch_id = batches[0].id;
        }
    }
};

const addItemRow = () => {
    form.items.push({
        product_id: '',
        batch_id: '',
        qty: 1,
        unit_id: '',
        unit_price: 0,
        discount_amount: 0,
        tax_amount: 0,
    });
};

const removeItemRow = (idx) => {
    form.items.splice(idx, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.qty || 0) * parseFloat(item.unit_price || 0)), 0);
});

const totalDiscount = computed(() => {
    return form.items.reduce((sum, item) => sum + parseFloat(item.discount_amount || 0), 0);
});

const grandTotal = computed(() => {
    return subtotal.value - totalDiscount.value;
});

const submit = () => {
    form.post(route('sales-records.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pencatatan Penjualan Baru" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('sales-records.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Penjualan
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Pencatatan Penjualan Produk</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Transaksi Header -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-cash-register text-success-600"></i> Informasi Transaksi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Lokasi Toko / Gudang <span class="text-danger-500">*</span></label>
                        <select v-model="form.location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi Toko --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tipe Transaksi <span class="text-danger-500">*</span></label>
                        <select v-model="form.record_type" required class="form-select">
                            <option value="individual">Penjualan Ritel / Nota Individu</option>
                            <option value="daily_recap">Rekapitulasi Penjualan Harian Toko</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tanggal Transaksi <span class="text-danger-500">*</span></label>
                        <input v-model="form.sold_at" type="date" required class="form-input text-xs" />
                    </div>

                    <div>
                        <label class="form-label">Nama Pembeli / Pelanggan</label>
                        <input v-model="form.customer_name" type="text" class="form-input" placeholder="contoh: Pak Haji Slamet (Kelompok Tani)" />
                    </div>

                    <div>
                        <label class="form-label">No. Telepon / WhatsApp Pembeli</label>
                        <input v-model="form.customer_contact" type="text" class="form-input" placeholder="0812xxxx" />
                    </div>

                    <div>
                        <label class="form-label">No. Referensi / Struk Manual</label>
                        <input v-model="form.reference_number" type="text" class="form-input" placeholder="contoh: NOTA/2026/001" />
                    </div>
                </div>
            </div>

            <!-- Items selection table -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-cart-flatbed text-accent-600"></i> Rincian Barang Terjual & Batch FEFO
                    </h3>
                    <button type="button" @click="addItemRow" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Baris Produk
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

                            <div class="md:col-span-2">
                                <label class="form-label text-xs">Pilih Batch (FEFO Teratas) <span class="text-danger-500">*</span></label>
                                <select v-model="item.batch_id" required class="form-select text-xs">
                                    <option value="">-- Pilih Batch Stok --</option>
                                    <option
                                        v-for="b in availableBatches(item.product_id, form.location_id)"
                                        :key="b.id"
                                        :value="b.id"
                                    >
                                        Batch: {{ b.batch_number }} (Stok: {{ b.remaining_qty }} | Exp: {{ $date(b.expiry_date) }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs">Qty Terjual <span class="text-danger-500">*</span></label>
                                <input v-model.number="item.qty" type="number" min="0.01" step="any" required class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Harga Jual Satuan (Rp) <span class="text-danger-500">*</span></label>
                                <input v-model.number="item.unit_price" type="number" min="0" step="500" required class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Diskon Nom. (Rp)</label>
                                <input v-model.number="item.discount_amount" type="number" min="0" step="500" class="form-input text-xs" />
                            </div>

                            <div>
                                <label class="form-label text-xs">Subtotal (Rp)</label>
                                <div class="form-input text-xs font-bold bg-surface-100 dark:bg-surface-800 text-right">
                                    {{ $currency((item.qty * item.unit_price) - (item.discount_amount || 0)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-accent-50/50 dark:bg-accent-950/30 border border-accent-200 dark:border-accent-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-surface-500 block">Subtotal: {{ $currency(subtotal) }} | Total Diskon: {{ $currency(totalDiscount) }}</span>
                        <span class="text-base font-bold text-surface-900 dark:text-white">Total Bayar Pembelian:</span>
                    </div>
                    <span class="text-3xl font-extrabold text-accent-600 dark:text-accent-400">{{ $currency(grandTotal) }}</span>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('sales-records.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-success text-base px-6 py-3">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Simpan & Potong Stok Penjualan</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
