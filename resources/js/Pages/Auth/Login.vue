<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Sistem" />

        <div class="mb-6">
            <h2 class="text-xl font-bold text-surface-900 dark:text-white">Masuk Akun</h2>
            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                Silakan masukkan email dan kata sandi Anda untuk mengakses dashboard.
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
                        class="form-input pl-10"
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
                        class="form-input pl-10"
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
                class="btn-primary w-full py-3 text-sm font-semibold shadow-lg shadow-primary-600/30 mt-2"
            >
                <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                <i v-else class="fa-solid fa-right-to-bracket"></i>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Quick Demo Credentials Hint -->
        <div class="mt-6 p-3 rounded-xl bg-surface-50 dark:bg-surface-800/60 border border-surface-200 dark:border-surface-700/60 text-xs text-surface-600 dark:text-surface-300">
            <p class="font-bold mb-1 text-surface-800 dark:text-white flex items-center gap-1.5">
                <i class="fa-solid fa-key text-warning-500"></i> Akun Demo Seeded:
            </p>
            <div class="grid grid-cols-2 gap-1 text-[11px] font-mono mt-1">
                <div><strong>Admin:</strong> admin@agrosaranatani.co.id</div>
                <div><strong>Owner:</strong> owner@agrosaranatani.co.id</div>
                <div><strong>Kep.Gudang:</strong> budi@agrosaranatani.co.id</div>
                <div><strong>Kep.Toko:</strong> sari@agrosaranatani.co.id</div>
            </div>
            <p class="text-[10px] text-surface-400 mt-1 italic">Password untuk semua akun: <code>password</code></p>
        </div>
    </GuestLayout>
</template>
