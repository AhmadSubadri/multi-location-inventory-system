<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    company: Object,
});

const previewLogoUrl = ref(props.company?.logo_url || null);

const form = useForm({
    name: props.company?.name || '',
    tagline: props.company?.tagline || '',
    address: props.company?.address || '',
    phone: props.company?.phone || '',
    email: props.company?.email || '',
    npwp: props.company?.npwp || '',
    default_tax_percent: props.company?.default_tax_percent || 11.0,
    currency_symbol: props.company?.currency_symbol || 'Rp',
    currency_code: props.company?.currency_code || 'IDR',
    logo: null,
});

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        previewLogoUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('company-profile.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Profil Perusahaan" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Profil & Identitas Perusahaan</h1>
            <p class="text-xs text-surface-500 mt-1">Data resmi & logo perusahaan yang tercetak pada kop struk, faktur, dan header aplikasi.</p>
        </div>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <!-- Logo Upload Section -->
                <div class="space-y-4">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-image text-primary-600"></i> Logo Resmi Perusahaan
                    </h3>

                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-surface-300 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 flex items-center justify-center overflow-hidden shrink-0 relative group">
                            <img v-if="previewLogoUrl" :src="previewLogoUrl" class="w-full h-full object-contain p-2" alt="Logo Company" />
                            <div v-else class="text-center text-surface-400 p-2">
                                <i class="fa-solid fa-building-flag text-2xl block mb-1"></i>
                                <span class="text-[10px]">No Logo</span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-2">
                            <label class="form-label">Unggah File Logo Baru</label>
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                @change="onFileChange"
                                class="block w-full text-xs text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-950 dark:file:text-primary-300 cursor-pointer"
                            />
                            <p class="text-[11px] text-surface-400">Format yang didukung: PNG, JPG, SVG, WEBP. Maksimal ukuran 2MB.</p>
                            <p v-if="form.errors.logo" class="form-error">{{ form.errors.logo }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-building text-primary-600"></i> Identitas Perusahaan
                    </h3>

                    <div>
                        <label class="form-label">Nama Perusahaan <span class="text-danger-500">*</span></label>
                        <input v-model="form.name" type="text" required class="form-input" placeholder="contoh: PT Agro Sarana Tani" />
                        <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="form-label">Slogan / Tagline</label>
                        <input v-model="form.tagline" type="text" class="form-input" placeholder="contoh: Solusi Lengkap Sarana Pertanian" />
                    </div>

                    <div>
                        <label class="form-label">Alamat Kantor Pusat</label>
                        <textarea v-model="form.address" rows="3" class="form-input" placeholder="Alamat lengkap perusahaan..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">No. Telepon / Fax</label>
                            <input v-model="form.phone" type="text" class="form-input" placeholder="0260-123456" />
                        </div>
                        <div>
                            <label class="form-label">Email Perusahaan</label>
                            <input v-model="form.email" type="email" class="form-input" placeholder="info@company.com" />
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Nomor NPWP</label>
                        <input v-model="form.npwp" type="text" class="form-input" placeholder="12.345.678.9-012.000" />
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <h3 class="font-bold text-base border-b border-surface-200 dark:border-surface-700 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-coins text-accent-600"></i> Pajak & Mata Uang Default
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Tarif PPN Default (%)</label>
                            <input v-model.number="form.default_tax_percent" type="number" step="0.1" min="0" max="100" class="form-input" />
                        </div>
                        <div>
                            <label class="form-label">Simbol Mata Uang</label>
                            <input v-model="form.currency_symbol" type="text" class="form-input font-bold" placeholder="Rp" />
                        </div>
                        <div>
                            <label class="form-label">Kode ISO Mata Uang</label>
                            <input v-model="form.currency_code" type="text" class="form-input font-mono uppercase" placeholder="IDR" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                        <span>Simpan Perubahan Profil & Logo</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
