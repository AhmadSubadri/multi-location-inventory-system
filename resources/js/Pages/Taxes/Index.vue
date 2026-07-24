<script setup>
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    taxes: Array,
});

const isModalOpen = ref(false);
const editingTax = ref(null);

const form = useForm({
    name: '',
    percent: 11,
    is_default: false,
    is_active: true,
});

const openCreateModal = () => {
    editingTax.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (t) => {
    editingTax.value = t;
    form.name = t.name;
    form.percent = t.percent;
    form.is_default = t.is_default;
    form.is_active = t.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingTax.value) {
        form.put(route('taxes.update', editingTax.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('taxes.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTax = (t) => {
    if (confirm(`Hapus tarif pajak "${t.name}"?`)) {
        router.delete(route('taxes.destroy', t.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Pajak PPN" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Pajak (PPN)</h1>
                <p class="text-xs text-surface-500 mt-1">Pengaturan tarif pajak transaksi penjualan dan pembelian.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Tarif Pajak
            </button>
        </div>

        <div class="card overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Tarif Pajak</th>
                        <th>Persentase (%)</th>
                        <th>Default Transaksi</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in taxes" :key="t.id">
                        <td class="font-bold text-surface-900 dark:text-white">{{ t.name }}</td>
                        <td class="font-mono font-bold text-primary-600">{{ t.percent }}%</td>
                        <td>
                            <span v-if="t.is_default" class="badge badge-warning">
                                <i class="fa-solid fa-star mr-1 text-[10px]"></i> Tarif Default
                            </span>
                            <span v-else class="text-xs text-surface-400">—</span>
                        </td>
                        <td>
                            <span class="badge" :class="t.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ t.is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                        <td class="text-right space-x-2">
                            <button @click="openEditModal(t)" class="btn-secondary btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button @click="deleteTax(t)" class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="isModalOpen" @close="closeModal" :title="editingTax ? 'Edit Pajak' : 'Tambah Pajak'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Pajak <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: PPN 11%" />
                </div>
                <div>
                    <label class="form-label">Persentase (%) <span class="text-danger-500">*</span></label>
                    <input v-model.number="form.percent" type="number" step="0.01" min="0" max="100" required class="form-input" />
                </div>
                <div class="space-y-2 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_default" class="rounded text-primary-600 w-4 h-4" />
                        <span class="text-xs font-semibold">Jadikan Tarif Default Utama</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" class="rounded text-primary-600 w-4 h-4" />
                        <span class="text-xs font-semibold">Status Aktif</span>
                    </label>
                </div>
            </form>
            <template #footer>
                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
