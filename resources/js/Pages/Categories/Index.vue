<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Object,
    allCategories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    parent_id: '',
    description: '',
});

const handleSearch = () => {
    router.get(route('categories.index'), { search: search.value }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (cat) => {
    editingCategory.value = cat;
    form.name = cat.name;
    form.parent_id = cat.parent_id || '';
    form.description = cat.description || '';
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (cat) => {
    if (confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"?`)) {
        router.delete(route('categories.destroy', cat.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kategori Produk" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Kategori Produk</h1>
                <p class="text-xs text-surface-500 mt-1">Kelola hierarki kategori produk sarana pertanian.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Kategori</span>
            </button>
        </div>

        <div class="card overflow-hidden">
            <!-- Search Filter Header -->
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-md relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari kategori..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Induk Kategori</th>
                            <th>Keterangan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories.data" :key="cat.id">
                            <td class="font-semibold text-surface-900 dark:text-white">
                                {{ cat.name }}
                            </td>
                            <td>
                                <span v-if="cat.parent" class="badge badge-info">
                                    <i class="fa-solid fa-folder-tree mr-1 text-[10px]"></i> {{ cat.parent.name }}
                                </span>
                                <span v-else class="text-surface-400 text-xs">— Top Level</span>
                            </td>
                            <td class="text-xs text-surface-500 max-w-xs truncate">
                                {{ cat.description || '-' }}
                            </td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(cat)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button @click="deleteCategory(cat)" class="btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="4" class="text-center py-8 text-surface-400 text-xs">
                                Tidak ada data kategori yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="categories.links" />
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" :title="editingCategory ? 'Edit Kategori' : 'Tambah Kategori'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Kategori <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Insektisida" />
                    <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="form-label">Induk Kategori (Opsional)</label>
                    <select v-model="form.parent_id" class="form-select">
                        <option value="">-- Kategori Utama (Tanpa Induk) --</option>
                        <option
                            v-for="c in allCategories"
                            :key="c.id"
                            :value="c.id"
                            :disabled="editingCategory && c.id === editingCategory.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.parent_id" class="form-error">{{ form.errors.parent_id }}</p>
                </div>

                <div>
                    <label class="form-label">Keterangan</label>
                    <textarea v-model="form.description" rows="3" class="form-input" placeholder="Deskripsi ringkas kategori..."></textarea>
                    <p v-if="form.errors.description" class="form-error">{{ form.errors.description }}</p>
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
