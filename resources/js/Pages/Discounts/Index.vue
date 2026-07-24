<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    discounts: Object,
    products: Array,
});

const isModalOpen = ref(false);
const editingDiscount = ref(null);

const form = useForm({
    name: '',
    type: 'percent',
    value: 0,
    product_id: '',
    start_date: '',
    end_date: '',
    is_active: true,
});

const openCreateModal = () => {
    editingDiscount.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (d) => {
    editingDiscount.value = d;
    form.name = d.name;
    form.type = d.type;
    form.value = d.value;
    form.product_id = d.product_id || '';
    form.start_date = d.start_date || '';
    form.end_date = d.end_date || '';
    form.is_active = d.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingDiscount.value) {
        form.put(route('discounts.update', editingDiscount.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('discounts.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteDiscount = (d) => {
    if (confirm(`Hapus diskon "${d.name}"?`)) {
        router.delete(route('discounts.destroy', d.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Diskon" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Promosi & Diskon</h1>
                <p class="text-xs text-surface-500 mt-1">Diskon persen / nominal per produk atau global.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Diskon
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Diskon</th>
                            <th>Tipe & Nilai</th>
                            <th>Target Produk</th>
                            <th>Periode Berlaku</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in discounts.data" :key="d.id">
                            <td class="font-bold text-surface-900 dark:text-white">{{ d.name }}</td>
                            <td>
                                <span class="badge" :class="d.type === 'percent' ? 'badge-info' : 'badge-success'">
                                    {{ d.type === 'percent' ? `${d.value}%` : $currency(d.value) }}
                                </span>
                            </td>
                            <td class="text-xs">{{ d.product?.name || 'Semua Produk (Global)' }}</td>
                            <td class="text-xs text-surface-500">
                                {{ d.start_date ? $date(d.start_date) : 'Langsung' }} s/d {{ d.end_date ? $date(d.end_date) : 'Selamanya' }}
                            </td>
                            <td>
                                <span class="badge" :class="d.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ d.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(d)" class="btn-secondary btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button @click="deleteDiscount(d)" class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr v-if="discounts.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Belum ada aturan diskon.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="discounts.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal" :title="editingDiscount ? 'Edit Diskon' : 'Tambah Diskon'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Promosi / Diskon <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Promo Musim Tanam 10%" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tipe Diskon</label>
                        <select v-model="form.type" class="form-select">
                            <option value="percent">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Nilai Diskon <span class="text-danger-500">*</span></label>
                        <input v-model.number="form.value" type="number" step="0.01" min="0" required class="form-input" />
                    </div>
                </div>
                <div>
                    <label class="form-label">Target Produk (Opsional)</label>
                    <select v-model="form.product_id" class="form-select">
                        <option value="">-- Semua Produk (Global) --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tgl Mulai</label>
                        <input v-model="form.start_date" type="date" class="form-input text-xs" />
                    </div>
                    <div>
                        <label class="form-label">Tgl Berakhir</label>
                        <input v-model="form.end_date" type="date" class="form-input text-xs" />
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer pt-2">
                    <input type="checkbox" v-model="form.is_active" class="rounded text-primary-600 w-4 h-4" />
                    <span class="text-xs font-semibold">Status Aktif</span>
                </label>
            </form>
            <template #footer>
                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
