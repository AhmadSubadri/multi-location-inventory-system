<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    locations: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
    role: '',
    locations: [],
    is_active: true,
});

const handleSearch = () => {
    router.get(route('users.index'), { search: search.value }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (u) => {
    editingUser.value = u;
    form.name = u.name;
    form.email = u.email;
    form.password = '';
    form.phone = u.phone || '';
    form.role = u.roles?.[0]?.name || '';
    form.locations = u.locations?.map((l) => l.id) || [];
    form.is_active = u.is_active;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (u) => {
    if (confirm(`Hapus pengguna "${u.name}"?`)) {
        router.delete(route('users.destroy', u.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Pengguna" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Pengguna Sistem & Hak Akses</h1>
                <p class="text-xs text-surface-500 mt-1">Kelola akun staf, kepala toko/gudang, owner, dan admin.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-md relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari nama atau email..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama & Email</th>
                            <th>Role / Jabatan</th>
                            <th>Akses Lokasi (Toko / Gudang)</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id">
                            <td>
                                <div class="font-bold text-surface-900 dark:text-white">{{ u.name }}</div>
                                <span class="text-xs text-surface-400 font-mono">{{ u.email }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info font-semibold">
                                    {{ u.roles?.[0]?.name || 'Tanpa Role' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="loc in u.locations" :key="loc.id" class="badge badge-neutral text-[10px]">
                                        {{ loc.name }}
                                    </span>
                                    <span v-if="!u.locations || u.locations.length === 0" class="text-xs text-surface-400">
                                        {{ u.roles?.[0]?.name === 'Super Admin' || u.roles?.[0]?.name === 'Owner' ? 'Akses Semua Lokasi' : 'Belum Ditugaskan' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-xs font-mono">{{ u.phone || '-' }}</td>
                            <td>
                                <span class="badge" :class="u.is_active ? 'badge-success' : 'badge-danger'">
                                    {{ u.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(u)" class="btn-secondary btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button @click="deleteUser(u)" class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-surface-400 text-xs">Belum ada pengguna terdaftar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="users.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal" :title="editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru'">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Budi Santoso" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email Pengguna <span class="text-danger-500">*</span></label>
                        <input v-model="form.email" type="email" required class="form-input" placeholder="budi@company.com" />
                    </div>
                    <div>
                        <label class="form-label">Kata Sandi {{ editingUser ? '(Kosongkan jika tak diubah)' : '*' }}</label>
                        <input v-model="form.password" type="password" :required="!editingUser" class="form-input" placeholder="••••••••" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Role Jabatan <span class="text-danger-500">*</span></label>
                        <select v-model="form.role" required class="form-select">
                            <option value="">-- Pilih Role --</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input v-model="form.phone" type="text" class="form-input" placeholder="0812xxxx" />
                    </div>
                </div>

                <div>
                    <label class="form-label">Penugasan Akses Lokasi (Gudang / Toko)</label>
                    <div class="grid grid-cols-2 gap-2 p-3 bg-surface-50 dark:bg-surface-800/50 rounded-xl border border-surface-200 dark:border-surface-700 max-h-40 overflow-y-auto">
                        <label v-for="loc in locations" :key="loc.id" class="flex items-center gap-2 cursor-pointer text-xs">
                            <input type="checkbox" :value="loc.id" v-model="form.locations" class="rounded text-primary-600 w-3.5 h-3.5" />
                            <span>{{ loc.name }}</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-surface-400 mt-1">Super Admin & Owner secara otomatis dapat mengakses seluruh lokasi.</p>
                </div>

                <label class="flex items-center gap-2 cursor-pointer pt-1">
                    <input type="checkbox" v-model="form.is_active" class="rounded text-primary-600 w-4 h-4" />
                    <span class="text-xs font-semibold">Akun Aktif (Dapat Login)</span>
                </label>
            </form>

            <template #footer>
                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>
