<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const page = usePage();
const company = computed(() => page.props.company || {});

// Dynamic Favicon Watcher
watch(
    () => company.value?.logo_url,
    (newLogo) => {
        if (newLogo && typeof document !== 'undefined') {
            let favicon = document.getElementById('dynamic-favicon');
            if (!favicon) {
                favicon = document.createElement('link');
                favicon.id = 'dynamic-favicon';
                favicon.rel = 'icon';
                document.head.appendChild(favicon);
            }
            favicon.href = newLogo;
        }
    },
    { immediate: true }
);
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-surface-900 via-primary-950 to-surface-900 p-4 sm:p-6">
        <div class="w-full sm:max-w-md">
            <!-- Brand Header with Dynamic Logo -->
            <div class="text-center mb-8">
                <div v-if="company.logo_url" class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2 shadow-xl shadow-primary-900/50 mb-4 ring-4 ring-white/10 overflow-hidden">
                    <img :src="company.logo_url" class="w-full h-full object-contain" alt="Logo Perusahaan" />
                </div>
                <div v-else class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary-600 to-accent-500 text-white shadow-xl shadow-primary-900/50 mb-4 ring-4 ring-white/10">
                    <i class="fa-solid fa-boxes-stacked text-3xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">
                    {{ company.name || 'PT Agro Sarana Tani' }}
                </h1>
                <p class="text-xs text-surface-400 mt-1 font-medium">
                    {{ company.tagline || 'Sistem Informasi Manajemen Inventory & Toko' }}
                </p>
            </div>

            <!-- Main Card -->
            <div class="bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-surface-700/50 p-6 sm:p-8">
                <slot />
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-xs text-surface-400">
                    &copy; {{ new Date().getFullYear() }} {{ company.name || 'Agro Inventory' }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>
