<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const action = ref(props.filters.action || '');

const handleSearch = () => {
    router.get(
        route('audit-logs.index'),
        { search: search.value, action: action.value },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Audit Log Aktivitas" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Audit Log Stream & Aktivitas Pengguna</h1>
                <p class="text-xs text-surface-500 mt-1">Jejak audit otomatis dari setiap aksi pembuatan, pengubahan, dan penghapusan data.</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="p-4 border-b border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/30 flex flex-wrap gap-3">
                <div class="max-w-xs relative flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Cari keterangan atau IP..."
                        class="form-input pl-9 text-xs"
                    />
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-surface-400 text-xs"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu (WIB)</th>
                            <th>Pengguna</th>
                            <th>Aksi (Action)</th>
                            <th>Keterangan Aktivitas</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id">
                            <td class="text-xs font-mono text-surface-500 whitespace-nowrap">
                                {{ $datetime(log.created_at) }}
                            </td>
                            <td class="font-bold text-xs text-surface-900 dark:text-white">
                                {{ log.user?.name || 'System / Guest' }}
                            </td>
                            <td>
                                <span class="badge badge-info font-mono text-[10px]">
                                    {{ log.action }}
                                </span>
                            </td>
                            <td class="text-xs text-surface-700 dark:text-surface-300 max-w-md">
                                {{ log.description }}
                            </td>
                            <td class="text-xs font-mono text-surface-400">
                                {{ log.ip_address || '-' }}
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="text-center py-8 text-surface-400 text-xs">Belum ada catatan aktivitas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="logs.links" />
        </div>
    </AuthenticatedLayout>
</template>
