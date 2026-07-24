<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    units: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const editingUnit = ref(null);

const form = useForm({
    name: '',
    symbol: '',
});

const handleSearch = () => {
    router.get(route('units.index'), { search: search.value }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingUnit.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (unit) => {
    editingUnit.value = unit;
    form.name = unit.name;
    form.symbol = unit.symbol;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingUnit.value) {
        form.put(route('units.update', editingUnit.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('units.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUnit = (unit) => {
    if (confirm(`Apakah Anda yakin ingin menghapus satuan "${unit.name}"?`)) {
        router.delete(route('units.destroy', unit.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Satuan" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Satuan Ukur</h1>
                <p class="text-xs text-surface-500 mt-1">Kelola satuan dasar (botol, sachet, dus, kg, liter, dll).</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Satuan</span>
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-md relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari satuan atau simbol..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Satuan</th>
                            <th>Simbol / Kode Short</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="unit in units.data" :key="unit.id">
                            <td class="font-semibold text-surface-900 dark:text-white">
                                {{ unit.name }}
                            </td>
                            <td>
                                <span class="badge badge-neutral font-mono uppercase">
                                    {{ unit.symbol }}
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(unit)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button @click="deleteUnit(unit)" class="btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="units.data.length === 0">
                            <td colspan="3" class="text-center py-8 text-surface-400 text-xs">
                                Tidak ada data satuan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="units.links" />
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" :title="editingUnit ? 'Edit Satuan' : 'Tambah Satuan'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Satuan <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Kilogram" />
                    <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="form-label">Simbol / Singkatan <span class="text-danger-500">*</span></label>
                    <input v-model="form.symbol" type="text" required class="form-input" placeholder="contoh: kg" />
                    <p v-if="form.errors.symbol" class="form-error">{{ form.errors.symbol }}</p>
                </div>
            </form>

            <template #footer>
                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary">
                    <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                    <span>Simpan</span>
                </button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
