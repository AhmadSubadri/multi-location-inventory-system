<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    locations: Array,
    products: Array,
    activeLocationId: Number,
});

const form = useForm({
    location_id: props.activeLocationId || props.locations?.[0]?.id || '',
    notes: '',
    items: [
        {
            product_id: '',
            batch_id: '',
            system_qty: 0,
            physical_qty: 0,
            notes: '',
        },
    ],
});

const availableBatches = (productId, locationId) => {
    if (!productId || !locationId) return [];
    const p = props.products.find((prod) => prod.id === productId);
    if (!p || !p.batches) return [];
    return p.batches.filter((b) => b.location_id === locationId);
};

const onBatchSelect = (idx) => {
    const bId = form.items[idx].batch_id;
    const pId = form.items[idx].product_id;
    const p = props.products.find((prod) => prod.id === pId);
    if (p && p.batches) {
        const b = p.batches.find((batch) => batch.id === bId);
        if (b) {
            form.items[idx].system_qty = parseFloat(b.remaining_qty);
            form.items[idx].physical_qty = parseFloat(b.remaining_qty);
        }
    }
};

const addItemRow = () => {
    form.items.push({
        product_id: '',
        batch_id: '',
        system_qty: 0,
        physical_qty: 0,
        notes: '',
    });
};

const removeItemRow = (idx) => {
    form.items.splice(idx, 1);
};

const submit = () => {
    form.post(route('stock-opnames.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buat Stok Opname" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('stock-opnames.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Opname
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Form Input Stok Opname Fisik</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-primary-600"></i> Lokasi Opname Fisik
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Lokasi Gudang / Toko <span class="text-danger-500">*</span></label>
                        <select v-model="form.location_id" required class="form-select">
                            <option value="">-- Pilih Lokasi --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Catatan Opname</label>
                        <input v-model="form.notes" type="text" class="form-input" placeholder="contoh: Opname rutin akhir bulan Juli" />
                    </div>
                </div>
            </div>

            <!-- Items & Physical Count -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-accent-600"></i> Perbandingan Stok Sistem vs Fisik
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
                            <select v-model="item.batch_id" @change="onBatchSelect(idx)" required class="form-select text-xs">
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
                            <label class="form-label text-xs">Stok Sistem</label>
                            <input v-model.number="item.system_qty" readonly type="number" class="form-input text-xs bg-surface-100 font-bold" />
                        </div>

                        <div class="w-28">
                            <label class="form-label text-xs">Hitung Fisik <span class="text-danger-500">*</span></label>
                            <input v-model.number="item.physical_qty" type="number" min="0" step="any" required class="form-input text-xs font-extrabold text-primary-600" />
                        </div>

                        <div class="w-28">
                            <label class="form-label text-xs">Selisih</label>
                            <div class="form-input text-xs font-bold text-right" :class="(item.physical_qty - item.system_qty) < 0 ? 'text-danger-600' : 'text-accent-600'">
                                {{ (item.physical_qty - item.system_qty) > 0 ? `+${item.physical_qty - item.system_qty}` : (item.physical_qty - item.system_qty) }}
                            </div>
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
                <Link :href="route('stock-opnames.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Simpan Draft Opname</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
