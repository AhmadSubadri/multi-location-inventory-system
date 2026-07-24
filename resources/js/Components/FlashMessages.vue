<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});

const dismissed = ref(false);

watch(flash, () => {
    dismissed.value = false;
}, { deep: true });
</script>

<template>
    <div v-if="!dismissed" class="space-y-3 mb-6">
        <!-- Success Alert -->
        <div
            v-if="flash.success"
            class="flex items-center justify-between p-4 rounded-xl bg-accent-50 border border-accent-200 text-accent-800 shadow-sm transition-all"
        >
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-accent-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-sm">Berhasil!</h4>
                    <p class="text-xs text-accent-700 mt-0.5">{{ flash.success }}</p>
                </div>
            </div>
            <button
                @click="dismissed = true"
                class="text-accent-500 hover:text-accent-800 text-sm p-1 rounded-lg transition-colors cursor-pointer"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Error Alert -->
        <div
            v-if="flash.error"
            class="flex items-center justify-between p-4 rounded-xl bg-danger-50 border border-danger-200 text-danger-800 shadow-sm transition-all"
        >
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-danger-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-sm">Terjadi Kesalahan!</h4>
                    <p class="text-xs text-danger-700 mt-0.5">{{ flash.error }}</p>
                </div>
            </div>
            <button
                @click="dismissed = true"
                class="text-danger-500 hover:text-danger-800 text-sm p-1 rounded-lg transition-colors cursor-pointer"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Warning Alert -->
        <div
            v-if="flash.warning"
            class="flex items-center justify-between p-4 rounded-xl bg-warning-50 border border-warning-200 text-warning-800 shadow-sm transition-all"
        >
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-warning-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-sm">Peringatan</h4>
                    <p class="text-xs text-warning-700 mt-0.5">{{ flash.warning }}</p>
                </div>
            </div>
            <button
                @click="dismissed = true"
                class="text-warning-500 hover:text-warning-800 text-sm p-1 rounded-lg transition-colors cursor-pointer"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Info Alert -->
        <div
            v-if="flash.info"
            class="flex items-center justify-between p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-800 shadow-sm transition-all"
        >
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-sm">Informasi</h4>
                    <p class="text-xs text-primary-700 mt-0.5">{{ flash.info }}</p>
                </div>
            </div>
            <button
                @click="dismissed = true"
                class="text-primary-500 hover:text-primary-800 text-sm p-1 rounded-lg transition-colors cursor-pointer"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
</template>
