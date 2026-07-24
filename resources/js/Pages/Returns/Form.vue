<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    locations: Array,
    suppliers: Array,
    products: Array,
});

const form = useForm({
    type: 'store_to_warehouse',
    location_id: props.locations?.[0]?.id || '',
    related_location_id: props.locations?.[1]?.id || '',
    supplier_id: props.suppliers?.[0]?.id || '',
    reason: 'Barang Rusak / Kemasan Bocor',
    notes: '',
    items: [
        {
            product_id: '',
            batch_id: '',
            qty: 1,
            condition: 'damaged',
        },
    ],
});

const availableBatches = (productId, locationId) => {
    if (!productId || !locationId) return [];
    const p = props.products.find((prod) => prod.id === productId);
    if (!p || !p.batches) return [];
    return p.batches.filter((b) => b.location_id === locationId);
};

const addItemRow = () => {
    form.items.push({
        product_id: '',
        batch_id: '',
        qty: 1,
        condition: 'damaged',
    });
};

const removeItemRow = (idx) => {
    form.items.splice(idx, 1);
};

const submit = () => {
    form.post(route('returns.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buat Dokumen Retur" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('returns.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Retur
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Form Pengajuan Retur Barang</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Scenario Selection Card -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-rotate-left text-warning-500"></i> Pilih Skenario & Lokasi Retur
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Skenario Retur <span class="text-danger-500">*</span></label>
                        <select v-model="form.type" required class="form-select font-bold">
                            <option value="store_to_warehouse">1. Retur Toko → Gudang Utama</option>
                            <option value="warehouse_to_supplier">2. Retur Gudang → Supplier Vendor</option>
                            <option value="customer_to_store">3. Retur Konsumen → Toko</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Lokasi Asal Retur <span class="text-danger-500">*</span></label>
                        <select v-model="form.location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi Asal --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <!-- Dependent Target Field -->
                    <div v-if="form.type === 'store_to_warehouse'">
                        <label class="form-label">Gudang Tujuan Retur <span class="text-danger-500">*</span></label>
                        <select v-model="form.related_location_id" required class="form-select">
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div v-if="form.type === 'warehouse_to_supplier'">
                        <label class="form-label">Supplier Vendor Tujuan <span class="text-danger-500">*</span></label>
                        <select v-model="form.supplier_id" required class="form-select">
                            <option value="">-- Pilih Supplier --</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Alasan Retur <span class="text-danger-500">*</span></label>
                    <input v-model="form.reason" type="text" required class="form-input" placeholder="contoh: Produk mendekati expired / Kemasan bocor / Salah kirim" />
                </div>
            </div>

            <!-- Items selection table -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-primary-600"></i> Baris Produk & Kondisi Fisik Retur
                    </h3>
                    <button type="button" @click="addItemRow" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <div v-for="(item, idx) in form.items" :key="idx" class="p-4 rounded-xl bg-surface-50 dark:bg-surface-800/40 border border-surface-200 dark:border-surface-700 flex flex-wrap md:flex-nowrap items-center gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <label class="form-label text-xs">Produk <span class="text-danger-500">*</span></label>
                            <select v-model="item.product_id" required class="form-select text-xs">
                                <option value="">-- Pilih Produk --</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="form-label text-xs">Batch <span class="text-danger-500">*</span></label>
                            <select v-model="item.batch_id" required class="form-select text-xs">
                                <option value="">-- Pilih Batch --</option>
                                <option
                                    v-for="b in availableBatches(item.product_id, form.location_id)"
                                    :key="b.id"
                                    :value="b.id"
                                >
                                    Batch: {{ b.batch_number }} (Stok: {{ b.remaining_qty }})
                                </option>
                            </select>
                        </div>

                        <div class="w-28">
                            <label class="form-label text-xs">Qty Retur <span class="text-danger-500">*</span></label>
                            <input v-model.number="item.qty" type="number" min="0.01" step="any" required class="form-input text-xs" />
                        </div>

                        <div class="w-36">
                            <label class="form-label text-xs">Kondisi Fisik <span class="text-danger-500">*</span></label>
                            <select v-model="item.condition" required class="form-select text-xs">
                                <option value="damaged">Rusak / Defect</option>
                                <option value="good">Bagus (Masih Baik)</option>
                            </select>
                        </div>

                        <div class="self-end pb-0.5">
                            <button v-if="form.items.length > 1" type="button" @click="removeItemRow(idx)" class="btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('returns.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Proses Retur & Potong/Tambah Stok</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
