<script setup>
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    title: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

watch(
    () => props.show,
    (val) => {
        if (val) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
    }
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
    full: 'sm:max-w-full sm:m-4',
}[props.maxWidth];
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="show"
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center bg-surface-900/60 backdrop-blur-xs"
                @click="close"
            >
                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        class="mb-6 bg-white dark:bg-surface-900 rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:w-full border border-surface-200 dark:border-surface-700"
                        :class="maxWidthClass"
                        @click.stop
                    >
                        <!-- Modal Header -->
                        <div v-if="title || $slots.title" class="px-6 py-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-surface-900 dark:text-white">
                                <slot name="title">{{ title }}</slot>
                            </h3>
                            <button
                                v-if="closeable"
                                @click="close"
                                class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 p-1.5 rounded-lg transition-colors cursor-pointer"
                            >
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-5 max-h-[75vh] overflow-y-auto">
                            <slot />
                        </div>

                        <!-- Modal Footer -->
                        <div v-if="$slots.footer" class="px-6 py-4 bg-surface-50 dark:bg-surface-800/50 border-t border-surface-200 dark:border-surface-700 flex items-center justify-end gap-3">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
