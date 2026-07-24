<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const editingSupplier = ref(null);

const form = useForm({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
});

const handleSearch = () => {
    router.get(route('suppliers.index'), { search: search.value }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingSupplier.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (sup) => {
    editingSupplier.value = sup;
    form.name = sup.name;
    form.contact_person = sup.contact_person || '';
    form.phone = sup.phone || '';
    form.email = sup.email || '';
    form.address = sup.address || '';
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('suppliers.update', editingSupplier.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSupplier = (sup) => {
    if (confirm(`Apakah Anda yakin ingin menghapus supplier "${sup.name}"?`)) {
        router.delete(route('suppliers.destroy', sup.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Supplier" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Master Supplier / Pemasok</h1>
                <p class="text-xs text-surface-500 mt-1">Kelola data vendor, distributor, dan produsen sarana tani.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Supplier</span>
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-md relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari nama, kontak, atau nomor telepon..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Pemasok</th>
                            <th>Contact Person</th>
                            <th>No. Telepon / HP</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sup in suppliers.data" :key="sup.id">
                            <td class="font-semibold text-surface-900 dark:text-white">
                                {{ sup.name }}
                            </td>
                            <td class="text-xs">{{ sup.contact_person || '-' }}</td>
                            <td class="text-xs font-mono">{{ sup.phone || '-' }}</td>
                            <td class="text-xs text-primary-600">{{ sup.email || '-' }}</td>
                            <td class="text-xs text-surface-500 max-w-xs truncate">{{ sup.address || '-' }}</td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(sup)" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button @click="deleteSupplier(sup)" class="btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="suppliers.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">
                                Tidak ada data supplier.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="suppliers.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal" :title="editingSupplier ? 'Edit Supplier' : 'Tambah Supplier'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Perusahaan / Vendor <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: PT Syngenta Indonesia" />
                    <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Contact Person</label>
                        <input v-model="form.contact_person" type="text" class="form-input" placeholder="Nama Sales/PIC" />
                    </div>

                    <div>
                        <label class="form-label">No. Telepon / WhatsApp</label>
                        <input v-model="form.phone" type="text" class="form-input" placeholder="0812xxxx" />
                    </div>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input v-model="form.email" type="email" class="form-input" placeholder="sales@vendor.com" />
                </div>

                <div>
                    <label class="form-label">Alamat Kantor / Gudang Vendor</label>
                    <textarea v-model="form.address" rows="3" class="form-input" placeholder="Alamat lengkap..."></textarea>
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
