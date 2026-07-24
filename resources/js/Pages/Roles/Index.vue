<script setup>
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Object, // grouped by module
});

const isModalOpen = ref(false);
const editingRole = ref(null);

const form = useForm({
    name: '',
    permissions: [],
});

const openCreateModal = () => {
    editingRole.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (r) => {
    editingRole.value = r;
    form.name = r.name;
    form.permissions = r.permissions?.map((p) => p.name) || [];
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const toggleModulePermissions = (moduleName, event) => {
    const modulePerms = props.permissions[moduleName].map((p) => p.name);
    if (event.target.checked) {
        // Add all
        modulePerms.forEach((p) => {
            if (!form.permissions.includes(p)) form.permissions.push(p);
        });
    } else {
        // Remove all
        form.permissions = form.permissions.filter((p) => !modulePerms.includes(p));
    }
};

const isModuleAllSelected = (moduleName) => {
    const modulePerms = props.permissions[moduleName].map((p) => p.name);
    return modulePerms.every((p) => form.permissions.includes(p));
};

const submit = () => {
    if (editingRole.value) {
        form.put(route('roles.update', editingRole.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRole = (r) => {
    if (confirm(`Hapus role "${r.name}"?`)) {
        router.delete(route('roles.destroy', r.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Role & Matriks Izin Akses" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Role Jabatan & Matriks Izin Hak Akses</h1>
                <p class="text-xs text-surface-500 mt-1">Konfigurasi hak akses granular (PRD §5 Matrix) per modul aplikasi.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Role Baru
            </button>
        </div>

        <!-- Role Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="r in roles" :key="r.id" class="card p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-3 mb-4">
                        <div>
                            <h3 class="font-extrabold text-lg text-surface-900 dark:text-white">{{ r.name }}</h3>
                            <span class="text-xs text-surface-400 font-medium">{{ r.permissions?.length || 0 }} Izin Aktif</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1 max-h-36 overflow-y-auto mb-4 p-1">
                        <span v-for="p in r.permissions?.slice(0, 8)" :key="p.id" class="badge badge-neutral text-[10px]">
                            {{ p.name }}
                        </span>
                        <span v-if="r.permissions?.length > 8" class="badge badge-info text-[10px]">
                            +{{ r.permissions.length - 8 }} izin lainnya
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-surface-100 dark:border-surface-800 pt-3">
                    <button @click="openEditModal(r)" class="btn-secondary btn-sm flex-1">
                        <i class="fa-solid fa-sliders"></i> Atur Matriks Izin
                    </button>
                    <button
                        @click="deleteRole(r)"
                        :disabled="['Super Admin', 'Owner', 'Kepala Gudang', 'Kepala Toko', 'Staff Gudang', 'Staff Toko'].includes(r.name)"
                        class="btn-danger btn-sm disabled:opacity-30"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Permissions Matrix Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="3xl" :title="editingRole ? `Matriks Izin Role: ${editingRole.name}` : 'Tambah Role Baru'">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="form-label">Nama Role / Jabatan <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Purchasing Officer" />
                </div>

                <div class="space-y-4">
                    <h4 class="font-bold text-sm text-surface-900 dark:text-white border-b border-surface-200 dark:border-surface-700 pb-2">
                        Pilih Hak Akses Per Modul (Matrix Control)
                    </h4>

                    <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2">
                        <div v-for="(perms, moduleName) in permissions" :key="moduleName" class="p-4 rounded-xl bg-surface-50 dark:bg-surface-800/40 border border-surface-200 dark:border-surface-700">
                            <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-2 mb-3">
                                <span class="font-bold text-xs uppercase tracking-wider text-primary-600 dark:text-primary-400">
                                    Modul: {{ moduleName.replace('_', ' ') }}
                                </span>
                                <label class="flex items-center gap-1.5 cursor-pointer text-xs text-surface-500 font-medium">
                                    <input
                                        type="checkbox"
                                        :checked="isModuleAllSelected(moduleName)"
                                        @change="toggleModulePermissions(moduleName, $event)"
                                        class="rounded text-primary-600 w-3.5 h-3.5"
                                    />
                                    <span>Pilih Semua</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <label v-for="p in perms" :key="p.id" class="flex items-center gap-2 cursor-pointer text-xs text-surface-700 dark:text-surface-300">
                                    <input
                                        type="checkbox"
                                        :value="p.name"
                                        v-model="form.permissions"
                                        class="rounded text-primary-600 w-3.5 h-3.5"
                                    />
                                    <span>{{ p.name.split('.')[1] }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary">Simpan Matriks Role</button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
