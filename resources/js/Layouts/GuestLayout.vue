<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage();
const company = computed(() => page.props.company || {});

const isDark = ref(false);

// Default high-res agricultural background if none uploaded
const defaultBgUrl = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1920&auto=format&fit=crop';

const bgStyle = computed(() => {
    const bgUrl = company.value?.login_bg_url || defaultBgUrl;
    return {
        backgroundImage: `linear-gradient(to right, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.70)), url('${bgUrl}')`,
    };
});

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

// Initialize & toggle Dark Mode
onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};
</script>

<template>
    <div
        class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4 sm:p-6 md:p-10 relative overflow-hidden transition-all duration-500 font-sans"
        :style="bgStyle"
    >
        <!-- Subtle Ambient Animated Gradients -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-600/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-accent-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Navigation Bar for Login Screen -->
        <header class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 flex items-center gap-3">
            <button
                @click="toggleDarkMode"
                type="button"
                class="p-2.5 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/20 transition-all cursor-pointer shadow-lg"
                :title="isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
            >
                <i v-if="isDark" class="fa-solid fa-sun text-warning-300 text-sm"></i>
                <i v-else class="fa-solid fa-moon text-surface-200 text-sm"></i>
            </button>
        </header>

        <!-- Main Glassmorphism Hero Container (Split on Desktop) -->
        <div class="w-full max-w-5xl bg-surface-900/60 dark:bg-surface-950/80 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/15 overflow-hidden grid grid-cols-1 lg:grid-cols-12 z-10 my-auto">
            <!-- Left Side: Brand & Feature Showcase (Hidden on Mobile) -->
            <div class="lg:col-span-6 p-8 sm:p-10 lg:p-12 flex flex-col justify-between bg-gradient-to-b from-primary-950/40 via-surface-950/50 to-surface-950/80 text-white border-b lg:border-b-0 lg:border-r border-white/10 relative">
                <div>
                    <!-- Dynamic Logo -->
                    <div class="flex items-center gap-3 mb-8">
                        <div v-if="company.logo_url" class="w-14 h-14 rounded-2xl bg-white p-1.5 shadow-xl ring-2 ring-white/20 overflow-hidden shrink-0">
                            <img :src="company.logo_url" class="w-full h-full object-contain" alt="Logo Perusahaan" />
                        </div>
                        <div v-else class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-500 to-accent-500 flex items-center justify-center text-white text-2xl font-black shadow-xl ring-2 ring-white/20 shrink-0">
                            <i class="fa-solid fa-wheat-awn"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-white leading-tight">
                                {{ company.name || 'PT Agro Sarana Tani' }}
                            </h2>
                            <p class="text-xs text-primary-300 font-medium tracking-wide">
                                {{ company.tagline || 'Sistem Distribusi & Inventory' }}
                            </p>
                        </div>
                    </div>

                    <!-- Welcome Headline -->
                    <div class="space-y-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-500/20 text-primary-300 border border-primary-400/30 text-xs font-semibold uppercase tracking-wider">
                            <i class="fa-solid fa-shield-halved text-xs"></i> Enterprise Security
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                            Manajemen Mutasi Stok & Multi-Lokasi
                        </h1>
                        <p class="text-xs sm:text-sm text-surface-300 leading-relaxed font-normal">
                            Kelola penerimaan gudang utama, transfer stok toko, pencatatan penjualan, hingga retur barang dengan pelacakan FEFO & audit ledger immutable.
                        </p>
                    </div>

                    <!-- Highlights List -->
                    <div class="space-y-3 text-xs text-surface-200 font-medium">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </div>
                            <span>Audit Trail Mutasi Stok Immutable & Anti-Selisih</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </div>
                            <span>Pelacakan Batch Produk & Expired Date (FEFO)</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/30">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </div>
                            <span>Switch Context Lokasi & Laporan Real-Time</span>
                        </div>
                    </div>
                </div>

                <!-- Footer copyright -->
                <div class="mt-8 pt-6 border-t border-white/10 text-[11px] text-surface-400 flex items-center justify-between">
                    <span>&copy; {{ new Date().getFullYear() }} {{ company.name || 'Agro Inventory' }}</span>
                    <span>v2.5 Enterprise</span>
                </div>
            </div>

            <!-- Right Side: Login Form Card -->
            <div class="lg:col-span-6 p-6 sm:p-10 flex flex-col justify-center bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl transition-colors">
                <slot />
            </div>
        </div>
    </div>
</template>
