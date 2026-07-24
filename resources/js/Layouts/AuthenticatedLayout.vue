<script setup>
import FlashMessages from '@/Components/FlashMessages.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth || {});
const user = computed(() => auth.value.user || {});
const company = computed(() => page.props.company || {});
const appVersion = computed(() => page.props.app_version || 'v1.2.0 Enterprise');

const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);
const locationDropdownOpen = ref(false);
const isDark = ref(false);

// PWA Install Prompt State
const deferredPrompt = ref(null);
const canInstallPwa = ref(false);

onMounted(() => {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        canInstallPwa.value = true;
    });
});

const installPwa = async () => {
    if (!deferredPrompt.value) return;
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        canInstallPwa.value = false;
    }
    deferredPrompt.value = null;
};

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

const activeLocationId = computed(() => auth.value.active_location_id);
const activeLocation = computed(() => {
    return user.value.locations?.find((l) => l.id === activeLocationId.value) || user.value.locations?.[0] || {};
});

// Helper for permission checks
const can = (permission) => {
    if (user.value.is_super_admin) return true;
    return user.value.permissions?.includes(permission);
};

// Switch active location
const switchLocation = (locId) => {
    locationDropdownOpen.value = false;
    router.post(route('switch-location'), { location_id: locId });
};

// Navigation item definitions according to PRD structure
const navigation = computed(() => [
    {
        name: 'Dashboard',
        route: 'dashboard',
        icon: 'fa-gauge-high',
        show: can('dashboard.view_all') || can('dashboard.view_own_location'),
    },
    {
        name: 'Panduan & SOP Sistem',
        route: 'user-guide.index',
        icon: 'fa-book-open',
        show: true,
    },
    {
        name: 'Master Data',
        icon: 'fa-folder-tree',
        show: can('products.view') || can('categories.view') || can('units.view') || can('suppliers.view') || can('prices.view'),
        children: [
            { name: 'Kategori Produk', route: 'categories.index', show: can('categories.view') },
            { name: 'Satuan', route: 'units.index', show: can('units.view') },
            { name: 'Master Produk', route: 'products.index', show: can('products.view') },
            { name: 'Daftar Harga', route: 'prices.index', show: can('prices.view') },
            { name: 'Pemasok / Supplier', route: 'suppliers.index', show: can('suppliers.view') },
            { name: 'Diskon', route: 'discounts.index', show: can('discounts.view') },
            { name: 'Pajak / PPN', route: 'taxes.index', show: can('taxes.view') },
        ],
    },
    {
        name: 'Operasional Stok',
        icon: 'fa-boxes-stacked',
        show: can('goods_receipts.view') || can('stock_transfers.view') || can('sales_records.view') || can('returns.view') || can('stock_opnames.view'),
        children: [
            { name: 'Penerimaan Barang', route: 'goods-receipts.index', show: can('goods_receipts.view') },
            { name: 'Transfer Stok', route: 'stock-transfers.index', show: can('stock_transfers.view') },
            { name: 'Pencatatan Penjualan', route: 'sales-records.index', show: can('sales_records.view') },
            { name: 'Retur Barang', route: 'returns.index', show: can('returns.view') },
            { name: 'Stok Opname', route: 'stock-opnames.index', show: can('stock_opnames.view') },
        ],
    },
    {
        name: 'Audit Trail / Stock Ledger',
        route: 'stock-ledgers.index',
        icon: 'fa-clock-rotate-left',
        show: can('stock_ledgers.view'),
    },
    {
        name: 'Laporan',
        icon: 'fa-chart-pie',
        show: can('reports.view'),
        children: [
            { name: 'Laporan Stok & Value', route: 'reports.stock', show: can('reports.view') },
            { name: 'Laporan Pergerakan Stok', route: 'reports.ledger', show: can('reports.view') },
            { name: 'Laporan Expiry / Kadaluarsa', route: 'reports.expiry', show: can('reports.view') },
            { name: 'Laporan Penjualan', route: 'reports.sales', show: can('reports.view') },
            { name: 'Laporan Penerimaan', route: 'reports.goods-receipts', show: can('reports.view') },
            { name: 'Laporan Transfer', route: 'reports.transfers', show: can('reports.view') },
            { name: 'Laporan Retur', route: 'reports.returns', show: can('reports.view') },
            { name: 'Laporan Fast / Slow Moving', route: 'reports.moving', show: can('reports.view') },
        ],
    },
    {
        name: 'Pengaturan Sistem',
        icon: 'fa-sliders',
        show: can('company_settings.view') || can('users.view') || can('roles.view') || can('locations.view'),
        children: [
            { name: 'Profil Perusahaan', route: 'company-profile.index', show: can('company_settings.view') },
            { name: 'Manajemen Lokasi', route: 'locations.index', show: can('locations.view') },
            { name: 'Pengguna System', route: 'users.index', show: can('users.view') },
            { name: 'Role & Izin Hak Akses', route: 'roles.index', show: can('roles.view') },
            { name: 'Audit Log Aktivitas', route: 'audit-logs.index', show: can('audit_logs.view') },
            { name: 'Konfigurasi Sistem', route: 'settings.index', show: can('company_settings.view') },
        ],
    },
]);

const isCurrentRoute = (routeName) => {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-surface-50 dark:bg-surface-950 flex flex-col font-sans transition-colors duration-200">
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-surface-950/60 backdrop-blur-xs z-40 lg:hidden transition-opacity"
        ></div>

        <!-- Sidebar Navigation Drawer -->
        <aside
            :class="[
                'fixed top-0 bottom-0 left-0 z-50 w-64 bg-surface-900 text-surface-100 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-surface-800 shadow-2xl lg:shadow-none',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Sidebar Header Brand -->
            <div class="h-16 px-5 flex items-center justify-between border-b border-surface-800 bg-surface-950/50">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <img
                        v-if="company.logo_url"
                        :src="company.logo_url"
                        class="w-9 h-9 rounded-xl object-contain bg-white p-1 shadow-md shadow-primary-950"
                        alt="Logo Perusahaan"
                    />
                    <div v-else class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary-600 to-accent-500 flex items-center justify-center text-white font-black text-lg shadow-md shadow-primary-900/50">
                        <i class="fa-solid fa-wheat-awn text-sm"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-sm tracking-tight text-white leading-none">
                            {{ company.name || 'AgroSarana Tani' }}
                        </h2>
                        <span class="text-[10px] text-primary-400 font-semibold tracking-wide uppercase">{{ appVersion }}</span>
                    </div>
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="lg:hidden text-surface-400 hover:text-white p-1 rounded-lg"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Active Location Selector Badge in Sidebar -->
            <div class="p-3 border-b border-surface-800/80 bg-surface-950/30">
                <div class="bg-surface-800/60 rounded-lg p-2.5 border border-surface-700/50">
                    <div class="flex items-center justify-between text-xs text-surface-400 mb-1 font-medium">
                        <span>LOKASI AKTIF:</span>
                        <span class="capitalize px-1.5 py-0.5 rounded bg-surface-700 text-[10px] text-surface-200">
                            {{ activeLocation.type === 'warehouse' ? 'Gudang' : 'Toko' }}
                        </span>
                    </div>
                    <div class="font-semibold text-xs text-white truncate flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-accent-400"></i>
                        <span>{{ activeLocation.name || 'Pilih Lokasi' }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
                <template v-for="(item, index) in navigation" :key="index">
                    <template v-if="item.show">
                        <!-- Single Route Link -->
                        <Link
                            v-if="!item.children"
                            :href="route(item.route)"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer"
                            :class="
                                isCurrentRoute(item.route)
                                    ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold shadow-md shadow-primary-950'
                                    : 'text-surface-300 hover:bg-surface-800 hover:text-white'
                            "
                        >
                            <i :class="['fa-solid', item.icon, 'w-5 text-center text-base']"></i>
                            <span>{{ item.name }}</span>
                        </Link>

                        <!-- Accordion Parent Group -->
                        <div v-else class="space-y-1">
                            <div
                                class="flex items-center justify-between px-3.5 py-2 rounded-lg text-xs font-bold text-surface-400 uppercase tracking-wider mt-3 mb-1"
                            >
                                <span class="flex items-center gap-2">
                                    <i :class="['fa-solid', item.icon, 'text-primary-400']"></i>
                                    {{ item.name }}
                                </span>
                            </div>
                            <div class="space-y-0.5 pl-2">
                                <template v-for="(child, childIdx) in item.children" :key="childIdx">
                                    <Link
                                        v-if="child.show"
                                        :href="route(child.route)"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                                        :class="
                                            isCurrentRoute(child.route)
                                                ? 'bg-primary-600/30 text-primary-300 border-l-2 border-primary-500 font-semibold'
                                                : 'text-surface-400 hover:bg-surface-800/60 hover:text-surface-200'
                                        "
                                    >
                                        <i class="fa-solid fa-angle-right text-[10px] text-surface-500"></i>
                                        <span>{{ child.name }}</span>
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </template>
                </template>
            </nav>

            <!-- User Footer in Sidebar with ASDEV Branding & App Version -->
            <div class="p-3 border-t border-surface-800 bg-surface-950/40">
                <div class="flex items-center gap-3 px-2 py-1.5">
                    <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ user.name }}</p>
                        <p class="text-[10px] text-surface-400 truncate">{{ user.roles?.[0] || 'User' }}</p>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-center text-surface-400 border-t border-surface-800/60 pt-2 font-medium flex flex-col gap-0.5">
                    <div class="flex items-center justify-center gap-1">
                        <i class="fa-solid fa-shield-halved text-accent-400"></i>
                        <span>Supported by <strong class="text-primary-300 font-bold">ASDEV Digital Solution</strong></span>
                    </div>
                    <div class="text-[9px] text-surface-500 font-mono">{{ appVersion }}</div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Top Navbar -->
            <header class="h-16 bg-white dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <!-- Left: Mobile Toggle & Page Context -->
                <div class="flex items-center gap-3">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 text-surface-600 hover:text-surface-900 dark:text-surface-300 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 cursor-pointer"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 hidden sm:inline-block">
                        {{ company.name }} — POS & Inventory System
                    </span>
                    <span class="hidden md:inline-block px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-mono text-[10px] font-bold border border-primary-200 dark:border-primary-800">
                        {{ appVersion }}
                    </span>
                </div>

                <!-- Right: PWA Install Button, Dark Mode Toggle, Location Switcher & User Profile -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- PWA Install Button (Shown if browser supports installation) -->
                    <button
                        v-if="canInstallPwa"
                        @click="installPwa"
                        type="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 text-surface-700 dark:text-surface-200 text-xs font-medium hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors cursor-pointer"
                        title="Install aplikasi ke HP / Mobile"
                    >
                        <i class="fa-solid fa-mobile-screen-button text-primary-600 dark:text-primary-400"></i>
                        <span class="hidden sm:inline">Install Aplikasi Mobile</span>
                    </button>

                    <!-- Dark / Light Mode Toggle Button -->
                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="p-2.5 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors cursor-pointer"
                        :title="isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
                    >
                        <i v-if="isDark" class="fa-solid fa-sun text-warning-400 text-sm"></i>
                        <i v-else class="fa-solid fa-moon text-surface-600 text-sm"></i>
                    </button>

                    <!-- Location Context Selector Dropdown -->
                    <div class="relative">
                        <button
                            @click="locationDropdownOpen = !locationDropdownOpen"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 text-surface-700 dark:text-surface-200 text-xs font-medium hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors cursor-pointer"
                        >
                            <i class="fa-solid fa-store text-primary-600 dark:text-primary-400"></i>
                            <span class="max-w-[140px] truncate hidden sm:inline">{{ activeLocation.name || 'Lokasi' }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-surface-400"></i>
                        </button>

                        <!-- Dropdown Menu for Location -->
                        <div
                            v-if="locationDropdownOpen"
                            @click="locationDropdownOpen = false"
                            class="fixed inset-0 z-40"
                        ></div>
                        <div
                            v-if="locationDropdownOpen"
                            class="absolute right-0 mt-2 w-56 bg-white dark:bg-surface-900 rounded-xl shadow-xl border border-surface-200 dark:border-surface-700 py-1.5 z-50 divide-y divide-surface-100 dark:divide-surface-800"
                        >
                            <div class="px-3 py-2 text-[11px] font-semibold text-surface-400 uppercase tracking-wider">
                                Switch Context Lokasi
                            </div>
                            <div class="py-1 max-h-56 overflow-y-auto">
                                <button
                                    v-for="loc in user.locations"
                                    :key="loc.id"
                                    @click="switchLocation(loc.id)"
                                    class="w-full px-3.5 py-2 text-left text-xs flex items-center justify-between hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors cursor-pointer"
                                    :class="loc.id === activeLocationId ? 'text-primary-600 font-bold bg-primary-50/50 dark:bg-primary-950/30' : 'text-surface-700 dark:text-surface-300'"
                                >
                                    <div class="flex items-center gap-2 truncate">
                                        <i class="fa-solid" :class="loc.type === 'warehouse' ? 'fa-warehouse text-warning-500' : 'fa-store text-accent-500'"></i>
                                        <span class="truncate">{{ loc.name }}</span>
                                    </div>
                                    <i v-if="loc.id === activeLocationId" class="fa-solid fa-check text-xs text-primary-600"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="relative">
                        <button
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors cursor-pointer"
                        >
                            <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-xs font-semibold text-surface-700 dark:text-surface-200 hidden md:inline">{{ user.name }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-surface-400"></i>
                        </button>

                        <div
                            v-if="userDropdownOpen"
                            @click="userDropdownOpen = false"
                            class="fixed inset-0 z-40"
                        ></div>
                        <div
                            v-if="userDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-900 rounded-xl shadow-xl border border-surface-200 dark:border-surface-700 py-1 z-50"
                        >
                            <div class="px-4 py-2 border-b border-surface-100 dark:border-surface-800">
                                <p class="text-xs font-bold text-surface-900 dark:text-white truncate">{{ user.name }}</p>
                                <p class="text-[10px] text-surface-400 truncate">{{ user.email }}</p>
                            </div>
                            <button
                                @click="logout"
                                class="w-full text-left px-4 py-2 text-xs text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 flex items-center gap-2 font-medium cursor-pointer"
                            >
                                <i class="fa-solid fa-right-from-bracket"></i> Logout / Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Inertia Flash Notification Alert -->
            <FlashMessages />

            <!-- Page Main Dynamic Content View -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 flex flex-col justify-between">
                <div>
                    <slot />
                </div>

                <!-- Global System Footer with ASDEV Digital Solution Branding & App Version -->
                <footer class="mt-12 border-t border-surface-200 dark:border-surface-800/80 pt-4 pb-2 text-[11px] text-surface-500 dark:text-surface-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span>&copy; {{ new Date().getFullYear() }} <strong class="font-semibold text-surface-700 dark:text-surface-200">{{ company.name || 'Agro Inventory' }}</strong>. All rights reserved.</span>
                        <span class="px-2 py-0.5 rounded bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 font-mono text-[10px] font-bold">{{ appVersion }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-medium">
                        <i class="fa-solid fa-laptop-code text-primary-500"></i>
                        <span>Supported & Developed by <strong class="text-primary-600 dark:text-primary-400 font-bold">ASDEV Digital Solution</strong></span>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>
