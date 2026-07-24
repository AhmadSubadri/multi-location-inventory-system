<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    locations: Array,
    products: Array,
    enableStoreToStore: Boolean,
});

const form = useForm({
    from_location_id: props.locations?.[0]?.id || '',
    to_location_id: props.locations?.[1]?.id || '',
    notes: '',
    items: [
        {
            product_id: '',
            batch_id: '',
            qty_sent: 1,
        },
    ],
});

const availableBatches = (productId, locationId) => {
    if (!productId || !locationId) return [];
    const p = props.products.find((prod) => prod.id === productId);
    if (!p || !p.batches) return [];
    return p.batches.filter((b) => b.location_id === locationId && parseFloat(b.remaining_qty) > 0);
};

const addItemRow = () => {
    form.items.push({
        product_id: '',
        batch_id: '',
        qty_sent: 1,
    });
};

const removeItemRow = (idx) => {
    form.items.splice(idx, 1);
};

const submit = () => {
    form.post(route('stock-transfers.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pengajuan Transfer Stok" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('stock-transfers.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Transfer
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Pengajuan Transfer Stok Baru</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Header Locations -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-route text-primary-600"></i> Asal & Tujuan Pengiriman
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Lokasi Asal (Stok Dikirim dari) <span class="text-danger-500">*</span></label>
                        <select v-model="form.from_location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi Asal --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }} ({{ l.type === 'warehouse' ? 'Gudang' : 'Toko' }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Lokasi Tujuan (Stok Diterima di) <span class="text-danger-500">*</span></label>
                        <select v-model="form.to_location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id" :disabled="l.id === form.from_location_id">{{ l.name }} ({{ l.type === 'warehouse' ? 'Gudang' : 'Toko' }})</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Catatan Pengiriman</label>
                    <input v-model="form.notes" type="text" class="form-input" placeholder="contoh: Permintaan penambahan stok toko Pamanukan" />
                </div>
            </div>

            <!-- Items & Batches selection -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-accent-600"></i> Pilih Produk & Batch Asal (FEFO)
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
                            <label class="form-label text-xs">Batch Asal (Sisa Stok) <span class="text-danger-500">*</span></label>
                            <select v-model="item.batch_id" required class="form-select text-xs">
                                <option value="">-- Pilih Batch --</option>
                                <option
                                    v-for="b in availableBatches(item.product_id, form.from_location_id)"
                                    :key="b.id"
                                    :value="b.id"
                                >
                                    Batch: {{ b.batch_number }} (Sisa: {{ b.remaining_qty }} | Exp: {{ $date(b.expiry_date) }})
                                </option>
                            </select>
                        </div>

                        <div class="w-32">
                            <label class="form-label text-xs">Qty Kirim <span class="text-danger-500">*</span></label>
                            <input v-model.number="item.qty_sent" type="number" min="0.01" step="any" required class="form-input text-xs" />
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
                <Link :href="route('stock-transfers.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Ajukan Transfer Stok</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
