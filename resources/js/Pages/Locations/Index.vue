<script setup>
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    locations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const editingLocation = ref(null);

const form = useForm({
    code: '',
    name: '',
    type: 'store',
    is_main_source: false,
    address: '',
    phone: '',
    pic_name: '',
    is_active: true,
});

const handleSearch = () => {
    router.get(route('locations.index'), { search: search.value }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingLocation.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (loc) => {
    editingLocation.value = loc;
    form.code = loc.code;
    form.name = loc.name;
    form.type = loc.type;
    form.is_main_source = loc.is_main_source;
    form.address = loc.address || '';
    form.phone = loc.phone || '';
    form.pic_name = loc.pic_name || '';
    form.is_active = loc.is_active;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (editingLocation.value) {
        form.put(route('locations.update', editingLocation.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('locations.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteLocation = (loc) => {
    if (confirm(`Apakah Anda yakin ingin menghapus lokasi "${loc.name}"?`)) {
        router.delete(route('locations.destroy', loc.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Lokasi" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Manajemen Gudang Utama & Toko Cabang</h1>
                <p class="text-xs text-surface-500 mt-1">Kelola daftar gudang pusat dan lokasi toko cabang ritel.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Lokasi
            </button>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30">
                <div class="max-w-md relative">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari kode, nama, atau PIC lokasi..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode & Nama Lokasi</th>
                            <th>Tipe Lokasi</th>
                            <th>Penanggung Jawab (PIC)</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="loc in locations.data" :key="loc.id">
                            <td>
                                <div class="flex items-center gap-2 font-bold text-surface-900 dark:text-white">
                                    <i class="fa-solid" :class="loc.type === 'warehouse' ? 'fa-warehouse text-warning-500' : 'fa-store text-accent-500'"></i>
                                    <span>{{ loc.name }}</span>
                                    <span v-if="loc.is_main_source" class="badge badge-warning text-[10px]">Gudang Utama</span>
                                </div>
                                <span class="text-[11px] font-mono text-surface-400 ml-6">Kode: {{ loc.code }}</span>
                            </td>
                            <td>
                                <span class="badge capitalize" :class="loc.type === 'warehouse' ? 'badge-warning' : 'badge-info'">
                                    {{ loc.type === 'warehouse' ? 'Gudang' : 'Toko Cabang' }}
                                </span>
                            </td>
                            <td class="text-xs">{{ loc.pic_name || '-' }}</td>
                            <td class="text-xs font-mono">{{ loc.phone || '-' }}</td>
                            <td class="text-xs text-surface-500 max-w-xs truncate">{{ loc.address || '-' }}</td>
                            <td>
                                <span class="badge" :class="loc.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ loc.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <button @click="openEditModal(loc)" class="btn-secondary btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button @click="deleteLocation(loc)" :disabled="loc.is_main_source" class="btn-danger btn-sm disabled:opacity-30"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr v-if="locations.data.length === 0">
                            <td colspan="7" class="text-center py-8 text-surface-400 text-xs">Belum ada lokasi terdaftar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="locations.links" />
        </div>

        <Modal :show="isModalOpen" @close="closeModal" :title="editingLocation ? 'Edit Lokasi' : 'Tambah Lokasi Baru'">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Kode Lokasi <span class="text-danger-500">*</span></label>
                        <input v-model="form.code" type="text" required class="form-input font-mono uppercase" placeholder="contoh: GDG-01 / TKO-01" />
                    </div>
                    <div>
                        <label class="form-label">Tipe Lokasi <span class="text-danger-500">*</span></label>
                        <select v-model="form.type" required class="form-select">
                            <option value="warehouse">Gudang (Warehouse)</option>
                            <option value="store">Toko (Store)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Nama Lokasi <span class="text-danger-500">*</span></label>
                    <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: Gudang Utama Subang" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Penanggung Jawab (PIC)</label>
                        <input v-model="form.pic_name" type="text" class="form-input" placeholder="Nama Kepala Gudang/Toko" />
                    </div>
                    <div>
                        <label class="form-label">No. Telepon / HP</label>
                        <input v-model="form.phone" type="text" class="form-input" placeholder="0260-xxxx" />
                    </div>
                </div>

                <div>
                    <label class="form-label">Alamat Lokasi</label>
                    <textarea v-model="form.address" rows="3" class="form-input" placeholder="Alamat lengkap..."></textarea>
                </div>

                <div class="space-y-2 pt-2">
                    <label v-if="form.type === 'warehouse'" class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_main_source" class="rounded text-warning-600 w-4 h-4" />
                        <span class="text-xs font-semibold text-warning-700">Set sebagai Gudang Utama (Sumber Utama Supplier)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" class="rounded text-primary-600 w-4 h-4" />
                        <span class="text-xs font-semibold">Status Lokasi Aktif</span>
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
