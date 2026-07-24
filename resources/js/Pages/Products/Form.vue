<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
    units: Array,
    suppliers: Array,
    locations: Array,
});

const isEdit = computed(() => !!props.product);

const form = useForm({
    sku: props.product?.sku || '',
    name: props.product?.name || '',
    category_id: props.product?.category_id || '',
    base_unit_id: props.product?.base_unit_id || '',
    description: props.product?.description || '',
    brand: props.product?.brand || '',
    active_ingredient: props.product?.active_ingredient || '',
    registration_number: props.product?.registration_number || '',
    is_hazardous: props.product?.is_hazardous || false,
    hazardous_notes: props.product?.hazardous_notes || '',
    image: null,
    suppliers: props.product?.suppliers?.map((s) => s.id) || [],
    prices: props.product?.prices?.map((p) => ({
        price_type: p.price_type,
        min_qty: p.min_qty,
        price: p.price,
        location_id: p.location_id,
    })) || [
        { price_type: 'purchase', min_qty: 1, price: 0, location_id: null },
        { price_type: 'retail', min_qty: 1, price: 0, location_id: null },
        { price_type: 'wholesale', min_qty: 10, price: 0, location_id: null },
    ],
    conversions: props.product?.unit_conversions?.map((c) => ({
        from_unit_id: c.from_unit_id,
        conversion_factor: c.conversion_factor,
    })) || [],
    stock_settings: props.locations?.map((loc) => {
        const existing = props.product?.stock_settings?.find((s) => s.location_id === loc.id);
        return {
            location_id: loc.id,
            location_name: loc.name,
            min_stock: existing ? existing.min_stock : 5,
        };
    }) || [],
});

import { computed } from 'vue';

const addPriceRow = () => {
    form.prices.push({
        price_type: 'retail',
        min_qty: 1,
        price: 0,
        location_id: null,
    });
};

const removePriceRow = (idx) => {
    form.prices.splice(idx, 1);
};

const addConversionRow = () => {
    form.conversions.push({
        from_unit_id: '',
        conversion_factor: 1,
    });
};

const removeConversionRow = (idx) => {
    form.conversions.splice(idx, 1);
};

const handleImageChange = (e) => {
    form.image = e.target.files[0];
};

const submit = () => {
    if (isEdit.value) {
        form.post(route('products.update', props.product.id), {
            _method: 'put',
        });
    } else {
        form.post(route('products.store'));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isEdit ? 'Edit Produk' : 'Tambah Produk Baru'" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('products.index')" class="text-xs text-surface-500 hover:text-surface-700 flex items-center gap-1 mb-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
                </Link>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">
                    {{ isEdit ? `Edit Produk: ${product.name}` : 'Tambah Produk Baru' }}
                </h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- 1. Informasi Dasar Produk -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-primary-600"></i> Informasi Utama & Detail Pertanian
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">SKU / Kode Produk <span class="text-danger-500">*</span></label>
                        <input v-model="form.sku" type="text" required class="form-input" placeholder="contoh: INS-001" />
                        <p v-if="form.errors.sku" class="form-error">{{ form.errors.sku }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Nama Produk <span class="text-danger-500">*</span></label>
                        <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Prevathon 50 SC 100ml" />
                        <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="form-label">Kategori <span class="text-danger-500">*</span></label>
                        <select v-model="form.category_id" required class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="form-error">{{ form.errors.category_id }}</p>
                    </div>

                    <div>
                        <label class="form-label">Satuan Dasar <span class="text-danger-500">*</span></label>
                        <select v-model="form.base_unit_id" required class="form-select">
                            <option value="">-- Pilih Satuan Dasar --</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol }})</option>
                        </select>
                        <p v-if="form.errors.base_unit_id" class="form-error">{{ form.errors.base_unit_id }}</p>
                    </div>

                    <div>
                        <label class="form-label">Merk / Brand</label>
                        <input v-model="form.brand" type="text" class="form-input" placeholder="contoh: FMC / Syngenta / Bayer" />
                    </div>

                    <div>
                        <label class="form-label">Bahan Aktif (Khusus Pestisida/Pupuk)</label>
                        <input v-model="form.active_ingredient" type="text" class="form-input" placeholder="contoh: Chlorantraniliprole 50 g/l" />
                    </div>

                    <div>
                        <label class="form-label">No. Registrasi / Izin Kementan</label>
                        <input v-model="form.registration_number" type="text" class="form-input" placeholder="contoh: RI. 01010120185001" />
                    </div>

                    <div>
                        <label class="form-label">Gambar Produk</label>
                        <input type="file" @change="handleImageChange" accept="image/*" class="form-input text-xs" />
                    </div>
                </div>

                <!-- B3 Dangerous Tag -->
                <div class="p-4 rounded-xl bg-danger-50/50 dark:bg-danger-950/20 border border-danger-200 dark:border-danger-800 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_hazardous" class="rounded border-danger-300 text-danger-600 focus:ring-danger-500 w-4 h-4" />
                        <span class="font-bold text-xs text-danger-700 dark:text-danger-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i> Produk Bahan Beracun & Berbahaya (B3 / Pestisida Keras)
                        </span>
                    </label>
                    <div v-if="form.is_hazardous">
                        <input v-model="form.hazardous_notes" type="text" class="form-input text-xs" placeholder="Petunjuk khusus penyimpanan / APD (misal: Simpan terpisah dari makanan, jauhkan dari anak-anak)" />
                    </div>
                </div>

                <div>
                    <label class="form-label">Deskripsi Lengkap</label>
                    <textarea v-model="form.description" rows="3" class="form-input" placeholder="Penjelasan cara pakai, dosis, atau deskripsi barang..."></textarea>
                </div>
            </div>

            <!-- 2. Multi-tier Pricing -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-surface-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-tags text-accent-600"></i> Penetapan Harga Bertingkat (Multi-tier Pricing)
                        </h3>
                        <p class="text-xs text-surface-500">Harga Beli, Harga Eceran (Retail), dan Harga Grosir per Qty Minimum.</p>
                    </div>
                    <button type="button" @click="addPriceRow" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Baris Harga
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tipe Harga</th>
                                <th>Min. Qty</th>
                                <th>Harga Nominal (Rp)</th>
                                <th>Khusus Lokasi (Opsional)</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, idx) in form.prices" :key="idx">
                                <td>
                                    <select v-model="p.price_type" required class="form-select text-xs">
                                        <option value="purchase">Harga Beli (HPP)</option>
                                        <option value="retail">Harga Eceran (Retail)</option>
                                        <option value="wholesale">Harga Grosir (Wholesale)</option>
                                    </select>
                                </td>
                                <td>
                                    <input v-model.number="p.min_qty" type="number" min="1" required class="form-input text-xs w-24" />
                                </td>
                                <td>
                                    <input v-model.number="p.price" type="number" min="0" step="500" required class="form-input text-xs" />
                                </td>
                                <td>
                                    <select v-model="p.location_id" class="form-select text-xs">
                                        <option :value="null">-- Berlaku Semua Lokasi --</option>
                                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <button type="button" @click="removePriceRow(idx)" class="btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Konversi Satuan (Multi-unit) -->
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-surface-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-calculator text-primary-600"></i> Konversi Satuan Bertingkat (Misal: 1 Dus = 24 Botol)
                        </h3>
                        <p class="text-xs text-surface-500">Konversi ke satuan dasar yang dipilih di atas.</p>
                    </div>
                    <button type="button" @click="addConversionRow" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Konversi Satuan
                    </button>
                </div>

                <div v-if="form.conversions.length > 0" class="space-y-3">
                    <div v-for="(c, idx) in form.conversions" :key="idx" class="flex items-center gap-3 bg-surface-50 dark:bg-surface-800/40 p-3 rounded-xl border border-surface-200 dark:border-surface-700">
                        <span class="text-xs font-semibold">1</span>
                        <select v-model="c.from_unit_id" required class="form-select text-xs w-48">
                            <option value="">-- Pilih Satuan Asal --</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol }})</option>
                        </select>
                        <span class="text-xs font-bold text-surface-500">=</span>
                        <input v-model.number="c.conversion_factor" type="number" step="0.01" min="0.01" required class="form-input text-xs w-32" placeholder="Faktor" />
                        <span class="text-xs text-surface-500 font-semibold">Satuan Dasar</span>
                        <button type="button" @click="removeConversionRow(idx)" class="btn-danger btn-sm ml-auto">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <p v-else class="text-xs text-surface-400 italic">Belum ada konversi satuan khusus untuk produk ini.</p>
            </div>

            <!-- 4. Minimum Stock Alert Per Location -->
            <div class="card p-6 space-y-4">
                <h3 class="font-bold text-base text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-warning-500"></i> Batas Stok Minimum (Reorder Point) per Gudang / Toko
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="ss in form.stock_settings" :key="ss.location_id">
                        <label class="form-label text-xs">{{ ss.location_name }}</label>
                        <input v-model.number="ss.min_stock" type="number" min="0" class="form-input text-xs" />
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('products.index')" class="btn-secondary">Batal</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Produk Baru' }}</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
