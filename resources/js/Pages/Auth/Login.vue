<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const company = computed(() => page.props.company || {});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const fillCredential = (email) => {
    form.email = email;
    form.password = 'password';
};

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Sistem" />

        <!-- Mobile Header with Dynamic Logo (visible on small screens) -->
        <div class="lg:hidden text-center mb-6">
            <div v-if="company.logo_url" class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white p-2 shadow-lg mb-3 ring-2 ring-primary-500/20">
                <img :src="company.logo_url" class="w-full h-full object-contain" alt="Logo Perusahaan" />
            </div>
            <div v-else class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-accent-500 text-white shadow-lg mb-3">
                <i class="fa-solid fa-wheat-awn text-2xl"></i>
            </div>
            <h2 class="text-xl font-extrabold text-surface-900 dark:text-white">
                {{ company.name || 'PT Agro Sarana Tani' }}
            </h2>
            <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">
                {{ company.tagline || 'Sistem Informasi Manajemen Inventory & Toko' }}
            </p>
        </div>

        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-surface-900 dark:text-white tracking-tight">Selamat Datang 👋</h2>
            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                Silakan masukkan kredensial akun Anda untuk mengakses sistem inventory.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email -->
            <div>
                <label for="email" class="form-label">Email Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-surface-400">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        placeholder="nama@agrosaranatani.co.id"
                        class="form-input pl-10 text-xs sm:text-sm py-2.5"
                        :class="{ 'border-danger-500': form.errors.email }"
                    />
                </div>
                <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-surface-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="form-input pl-10 text-xs sm:text-sm py-2.5"
                        :class="{ 'border-danger-500': form.errors.password }"
                    />
                </div>
                <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded border-surface-300 text-primary-600 focus:ring-primary-500 w-4 h-4"
                    />
                    <span class="text-xs text-surface-600 dark:text-surface-300 font-medium">Ingat Saya di Perangkat Ini</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="btn-primary w-full py-3 text-sm font-bold shadow-xl shadow-primary-600/30 mt-2 cursor-pointer transition-all hover:scale-[1.01]"
            >
                <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                <i v-else class="fa-solid fa-right-to-bracket"></i>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Quick Demo Credentials Hint (Clickable 1-Tap Fill) -->
        <div class="mt-6 p-3.5 rounded-2xl bg-surface-50 dark:bg-surface-800/60 border border-surface-200 dark:border-surface-700/60 text-xs">
            <p class="font-bold text-surface-800 dark:text-white flex items-center justify-between mb-2">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-bolt text-warning-500"></i> Akun Demo (Klik untuk Isi Automatic):</span>
                <span class="text-[10px] text-surface-400 font-normal">Pass: password</span>
            </p>
            <div class="grid grid-cols-2 gap-1.5">
                <button
                    @click="fillCredential('admin@agrosaranatani.co.id')"
                    type="button"
                    class="p-1.5 rounded-lg bg-white dark:bg-surface-700 hover:bg-primary-50 dark:hover:bg-primary-950 text-left border border-surface-200 dark:border-surface-600 transition-colors cursor-pointer"
                >
                    <div class="font-bold text-[11px] text-primary-600 dark:text-primary-400">Super Admin</div>
                    <div class="text-[10px] text-surface-500 truncate">admin@...</div>
                </button>
                <button
                    @click="fillCredential('owner@agrosaranatani.co.id')"
                    type="button"
                    class="p-1.5 rounded-lg bg-white dark:bg-surface-700 hover:bg-accent-50 dark:hover:bg-accent-950 text-left border border-surface-200 dark:border-surface-600 transition-colors cursor-pointer"
                >
                    <div class="font-bold text-[11px] text-accent-600 dark:text-accent-400">Owner / Direktur</div>
                    <div class="text-[10px] text-surface-500 truncate">owner@...</div>
                </button>
                <button
                    @click="fillCredential('budi@agrosaranatani.co.id')"
                    type="button"
                    class="p-1.5 rounded-lg bg-white dark:bg-surface-700 hover:bg-warning-50 dark:hover:bg-warning-950 text-left border border-surface-200 dark:border-surface-600 transition-colors cursor-pointer"
                >
                    <div class="font-bold text-[11px] text-warning-600 dark:text-warning-400">Kepala Gudang</div>
                    <div class="text-[10px] text-surface-500 truncate">budi@...</div>
                </button>
                <button
                    @click="fillCredential('sari@agrosaranatani.co.id')"
                    type="button"
                    class="p-1.5 rounded-lg bg-white dark:bg-surface-700 hover:bg-success-50 dark:hover:bg-success-950 text-left border border-surface-200 dark:border-surface-600 transition-colors cursor-pointer"
                >
                    <div class="font-bold text-[11px] text-success-600 dark:text-success-400">Kepala Toko</div>
                    <div class="text-[10px] text-surface-500 truncate">sari@...</div>
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
