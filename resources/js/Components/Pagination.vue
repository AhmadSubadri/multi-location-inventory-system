<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-t border-surface-200 bg-white dark:bg-surface-900 dark:border-surface-700 rounded-b-xl">
        <div class="flex flex-wrap gap-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-3 py-1.5 text-xs text-surface-400 border border-surface-200 rounded-lg cursor-not-allowed dark:border-surface-700"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="px-3 py-1.5 text-xs rounded-lg transition-colors border"
                    :class="
                        link.active
                            ? 'bg-primary-600 text-white border-primary-600 font-medium shadow-xs'
                            : 'bg-white text-surface-700 border-surface-200 hover:bg-surface-50 dark:bg-surface-800 dark:text-surface-300 dark:border-surface-700 dark:hover:bg-surface-700'
                    "
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
