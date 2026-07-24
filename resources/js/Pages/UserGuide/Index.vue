<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('roles'); // roles, sops, modules, faqs
const searchQuery = ref('');

const rolesGuide = [
    {
        title: 'Super Admin',
        icon: 'fa-user-shield',
        color: 'text-purple-500 bg-purple-100 dark:bg-purple-950/60 dark:text-purple-300',
        scope: 'Akses Global Seluruh Sistem',
        tasks: [
            'Mengelola Pengaturan Profil & Logo Perusahaan.',
            'Menambah & Mengonfigurasi Lokasi Baru (Gudang/Toko).',
            'Manajemen Akun Pengguna, Pembagian Role, dan Izin Hak Akses.',
            'Mengatur Penomoran Dokumen & Toleransi Peringatan Kadaluarsa.',
            'Memantau Audit Log Aktivitas seluruh pengguna.',
        ],
        workflow: 'Melakukan penyiapan awal master data, pendaftaran user baru, penentuan role, serta pengawasan aktivitas sistem.',
    },
    {
        title: 'Owner / Direktur',
        icon: 'fa-user-tie',
        color: 'text-blue-500 bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300',
        scope: 'Akses Global Executive (Read-Only & Monitoring)',
        tasks: [
            'Memantau ringkasan nilai persediaan & stok kritis di Dashboard.',
            'Mengecek Laporan Penjualan harian/bulanan seluruh toko cabang.',
            'Melihat Laporan Pergerakan Stok, Fast/Slow Moving, & Expiry.',
            'Melakukan Switch Context Lokasi untuk meninjau data per toko/gudang.',
        ],
        workflow: 'Login harian/mingguan untuk melihat performa persediaan barang, tren penjualan, dan evaluasi nilai persediaan tanpa mengedit operasional.',
    },
    {
        title: 'Kepala Gudang',
        icon: 'fa-warehouse',
        color: 'text-amber-500 bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300',
        scope: 'Operasional Gudang Utama',
        tasks: [
            'Memeriksa & Menyutujui (Approve) Penerimaan Barang dari Supplier.',
            'Menyetujui (Approve) & Mengonfirmasi Pengiriman Transfer ke Toko.',
            'Melakukan persetujuan Stok Opname Gudang.',
            'Memantau umur simpan barang (Expiry/Kadaluarsa) di Gudang.',
        ],
        workflow: 'Menerima barang dari supplier → Approve Goods Receipt → Memproses pengiriman transfer ke toko saat ada permintaan → Approve penyesuaian stok.',
    },
    {
        title: 'Staff / Admin Gudang',
        icon: 'fa-boxes-packing',
        color: 'text-orange-500 bg-orange-100 dark:bg-orange-950/60 dark:text-orange-300',
        scope: 'Entri Data Gudang Utama',
        tasks: [
            'Input fisik penerimaan barang dari supplier (Supplier, Qty, Batch, Expiry).',
            'Input permohonan / draft pengiriman transfer stok ke toko.',
            'Input penghitungan fisik barang saat Stok Opname Gudang.',
        ],
        workflow: 'Input dokumen Penerimaan Barang saat truck supplier datang → Input draft Transfer Stok saat akan mengirim barang ke toko.',
    },
    {
        title: 'Kepala Toko',
        icon: 'fa-store',
        color: 'text-emerald-500 bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300',
        scope: 'Operasional Toko Cabang',
        tasks: [
            'Mengonfirmasi Penerimaan Barang Transfer dari Gudang Utama.',
            'Menyetujui (Approve) Retur Barang dari Pembeli / Customer.',
            'Menyetujui Stok Opname Toko.',
            'Mengecek laporan penjualan & persediaan toko miliknya.',
        ],
        workflow: 'Menerima kiriman barang dari gudang → Konfirmasi Qty diterima → Approve retur barang pembeli → Monitor stok minimum toko.',
    },
    {
        title: 'Staff / Kasir Toko',
        icon: 'fa-cash-register',
        color: 'text-teal-500 bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300',
        scope: 'Entri Penjualan & Retur Toko',
        tasks: [
            'Input Pencatatan Stok Keluar (Penjualan) harian/per nota.',
            'Input pencatatan Retur Barang dari Customer (Kondisi Baik/Rusak).',
            'Input hasil hitung fisik barang pada Stok Opname Toko.',
        ],
        workflow: 'Mencatat setiap transaksi penjualan pembeli → Sistem otomatis memotong stok toko & memilih harga grosir jika Qty memenuhi syarat.',
    },
];

const sops = [
    {
        id: 1,
        title: '1. Penerimaan Barang dari Supplier ke Gudang Utama',
        actor: 'Staff Gudang (Create) & Kepala Gudang (Approve)',
        steps: [
            { title: 'Buka Menu Operasional', desc: 'Pilih menu Operasional Stok → Penerimaan Barang → Klik "Tambah Penerimaan Baru".' },
            { title: 'Pilih Supplier & Gudang', desc: 'Pilih pemasok barang dan lokasi gudang penerima.' },
            { title: 'Input Item Barang & Batch', desc: 'Masukkan SKU/Produk, Qty diterima, Satuan, Harga Beli, Nomor Batch/Lot, dan Tanggal Kadaluarsa.' },
            { title: 'Simpan sebagai Draft', desc: 'Dokumen akan berstatus DRAFT dan stok belum bertambah.' },
            { title: 'Pemeriksaan & Approval', desc: 'Kepala Gudang mengecek kesesuaian fisik, lalu klik tombol "Setujui & Terima Barang".' },
            { title: 'Efek Sistem', desc: 'Stok Gudang otomatis bertambah, batch baru terbentuk, dan kartu stok (Stock Ledger) mencatat entri IN.' },
        ],
    },
    {
        id: 2,
        title: '2. Transfer Stok dari Gudang Utama ke Toko Cabang',
        actor: 'Staff/Kepala Gudang & Kepala Toko',
        steps: [
            { title: 'Buat Dokumen Transfer', desc: 'Pilih Operasional Stok → Transfer Stok → Klik "Buat Transfer Stok".' },
            { title: 'Tentukan Asal & Tujuan', desc: 'Pilih Lokasi Asal (Gudang Utama) dan Lokasi Tujuan (Toko Cabang).' },
            { title: 'Pilih Produk & Batch FEFO', desc: 'Pilih produk dan ketersediaan batch (sistem otomatis merekomendasikan stok terdekat kadaluarsa).' },
            { title: 'Approval Gudang & Pengiriman', desc: 'Kepala Gudang menyetujui dan mengubah status menjadi "Dikirim". Stok Gudang otomatis berkurang.' },
            { title: 'Penerimaan oleh Toko', desc: 'Kepala/Staff Toko membuka dokumen transfer di lokasi toko tujuan, memverifikasi Qty fisik, lalu klik "Konfirmasi Diterima".' },
            { title: 'Efek Sistem', desc: 'Stok Toko tujuan bertambah dan tercatat di kartu stok (Stock Ledger) sebagai TRANSFER_IN.' },
        ],
    },
    {
        id: 3,
        title: '3. Pencatatan Penjualan / Stok Keluar Toko',
        actor: 'Staff / Admin Toko',
        steps: [
            { title: 'Buka Menu Penjualan', desc: 'Pilih Operasional Stok → Pencatatan Penjualan → Klik "Catat Penjualan".' },
            { title: 'Input Data Pelanggan (Opsional)', desc: 'Isi nama pembeli / nomor referensi nota jika diperlukan.' },
            { title: 'Input Produk & Qty Keluar', desc: 'Masukkan item produk dan kuantitas yang dibeli customer.' },
            { title: 'Penerapan Harga Grosir Otomatis', desc: 'Jika Qty ≥ ambang minimum grosir, sistem secara otomatis memilih tingkat Harga Grosir.' },
            { title: 'Simpan Penjualan', desc: 'Klik Simpan. Stok Toko langsung berkurang dan tercatat di Stock Ledger sebagai OUT_SALE.' },
        ],
    },
    {
        id: 4,
        title: '4. Prosedur Retur Barang',
        actor: 'Staff Toko & Kepala Toko / Kepala Gudang',
        steps: [
            { title: 'Retur Pembeli ke Toko', desc: 'Staff Toko input Dokumen Retur (Tipe: Customer ke Toko). Pilih kondisi barang (Baik / Rusak). Barang kondisi baik akan menambah stok toko.' },
            { title: 'Retur Toko ke Gudang', desc: 'Toko mengembalikan barang berlebih/rusak ke Gudang Utama. Mengurangi stok toko dan menambah stok gudang setelah di-approve.' },
            { title: 'Approval Mandiri', desc: 'Setiap retur membutuhkan persetujuan Kepala Lokasi penerima sebelum stok diproses.' },
        ],
    },
    {
        id: 5,
        title: '5. Stok Opname & Penyesuaian Ledger',
        actor: 'Staff Lokasi & Kepala Lokasi',
        steps: [
            { title: 'Buat Sesi Opname', desc: 'Pilih Operasional Stok → Stok Opname → Tentukan lokasi & tanggal opname.' },
            { title: 'Input Hasil Hitung Fisik', desc: 'Petugas memasukkan angka Qty Fisik hasil perhitungan riil di lapangan.' },
            { title: 'Hitung Selisih Otomatis', desc: 'Sistem menampilkan selisih (Fisik - Sistem) beserta catatan alasan penyesuaian.' },
            { title: 'Approval Penyesuaian', desc: 'Kepala Lokasi memeriksa dan menyetujui. Sistem membuat entri ADJUSTMENT (+/-) di Stock Ledger sehingga stok sistem = stok fisik.' },
        ],
    },
];

const modulesGuide = [
    { name: 'Dashboard', desc: 'Pusat informasi visual stok kritis, nilai persediaan, notifikasi kadaluarsa, & grafik aktivitas.' },
    { name: 'Master Produk & Batch', desc: 'Katalog barang lengkap dengan SKU, kategori, satuan dasar, kandungan B3, serta pelacakan batch & kadaluarsa FEFO.' },
    { name: 'Daftar Harga & Grosir', desc: 'Pengaturan multi-tier harga eceran & harga grosir berdasarkan kuantitas beli minimum.' },
    { name: 'Penerimaan Barang', desc: 'Formulir suplai barang masuk dari vendor/supplier ke gudang utama.' },
    { name: 'Transfer Stok', desc: 'Pengiriman barang antar gudang dan toko cabang dengan validasi stok tidak boleh minus.' },
    { name: 'Pencatatan Penjualan', desc: 'Formulir pengurangan stok akibat transaksi penjualan di toko cabang.' },
    { name: 'Retur Barang', desc: 'Pencatatan pengembalian barang dari pembeli atau antar cabang.' },
    { name: 'Stok Opname', desc: 'Fitur audit independen untuk mencocokkan stok fisik dengan stok komputer.' },
    { name: 'Stock Ledger (Kartu Stok)', desc: 'Jejak audit permanen & terpusat yang mencatat setiap mutasi barang tanpa pernah bisa diedit/dihapus.' },
    { name: 'Laporan & Rekapitulasi', desc: '8 jenis laporan analisis persediaan, penjualan, fast/slow moving, & expiry yang dapat diekspor ke Excel/PDF.' },
    { name: 'Profil Perusahaan & Logo', desc: 'Pengaturan identitas bisnis, logo resmi, NPWP, pajak PPN default, & simbol mata uang.' },
    { name: 'Audit Log Aktivitas', desc: 'Catatan rekam jejak pengguna (login, logout, edit data, persetujuan).' },
];

const faqs = [
    {
        q: 'Mengapa saya tidak bisa memindahkan stok melebihi jumlah di Gudang?',
        a: 'Sistem menerapkan aturan validasi ketat "Stok Dilarang Minus". Anda tidak dapat membuat transfer atau penjualan melebihi sisa stok fisik yang tercatat pada batch terpilih.',
    },
    {
        q: 'Bagaimana cara menambahkan Toko Cabang atau Gudang baru di kemudian hari?',
        a: 'Super Admin cukup membuka menu Pengaturan Sistem → Manajemen Lokasi → Klik "Tambah Lokasi Baru". Masukkan Nama & Tipe (Warehouse/Store). Sistem akan otomatis memperbarui seluruh dropdown tanpa perlu bongkar kode program.',
    },
    {
        q: 'Mengapa akun Kepala Toko saya tidak bisa melihat data Toko Cabang lain?',
        a: 'Sistem menerapkan pembatasan hak akses berbasis lokasi (*Location-scoped Isolation*). Kepala Toko hanya berhak melihat data tokonya sendiri untuk menjaga privasi operasional.',
    },
    {
        q: 'Bagaimana cara mengaktifkan Harga Grosir saat penjualan?',
        a: 'Atur terlebih dahulu kuantitas minimum grosir pada menu Master Data → Daftar Harga. Ketika Staff Toko menginput Qty penjualan yang memenuhi syarat tersebut, sistem akan memilih harga grosir secara otomatis.',
    },
    {
        q: 'Kapan sebaiknya Stok Opname dilakukan?',
        a: 'Stok Opname disarankan dilakukan secara rutin (misal tiap akhir minggu/bulan) atau saat terjadi dugaan selisih barang fisik. Setiap selisih yang disetujui akan tercatat transparan pada Stock Ledger.',
    },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Panduan & Edukasi Sistem" />

        <!-- Header Title Banner -->
        <div class="mb-6 bg-gradient-to-r from-primary-900 via-primary-800 to-surface-900 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-primary-200 text-xs font-semibold mb-2">
                        <i class="fa-solid fa-graduation-cap"></i> Center Edukasi & SOP Operational
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Panduan Penggunaan Sistem Inventory</h1>
                    <p class="text-xs text-primary-200 mt-1 max-w-2xl">
                        Petunjuk lengkap alur kerja, tugas berdasarkan role akun, alur operasional barang (SOP), serta direktori modul sistem.
                    </p>
                </div>
                <div class="shrink-0 bg-white/10 p-3.5 rounded-2xl backdrop-blur-md hidden sm:block">
                    <i class="fa-solid fa-book-open text-4xl text-primary-300"></i>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 border-b border-surface-200 dark:border-surface-800 mb-6 pb-2">
            <button
                @click="activeTab = 'roles'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'roles' ? 'bg-primary-600 text-white shadow-md' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200'"
            >
                <i class="fa-solid fa-users"></i> Panduan Per Role Akun
            </button>
            <button
                @click="activeTab = 'sops'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'sops' ? 'bg-primary-600 text-white shadow-md' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200'"
            >
                <i class="fa-solid fa-diagram-project"></i> Alur Prosedur SOP Barang
            </button>
            <button
                @click="activeTab = 'modules'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'modules' ? 'bg-primary-600 text-white shadow-md' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200'"
            >
                <i class="fa-solid fa-cubes"></i> Kamus Modul & Fitur
            </button>
            <button
                @click="activeTab = 'faqs'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'faqs' ? 'bg-primary-600 text-white shadow-md' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200'"
            >
                <i class="fa-solid fa-circle-question"></i> FAQ & Problem Solving
            </button>
        </div>

        <!-- TAB 1: PANDUAN BERDASARKAN ROLE -->
        <div v-if="activeTab === 'roles'" class="space-y-6">
            <div class="card p-4 bg-primary-50/50 dark:bg-primary-950/30 border-primary-200 dark:border-primary-800/50 flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-primary-600 text-xl shrink-0"></i>
                <p class="text-xs text-primary-900 dark:text-primary-200">
                    Sistem mengelompokkan tugas berdasarkan Role Akun. Pastikan Anda memahami tanggung jawab utama dan alur kerja harian sesuai dengan peran Anda di dalam perusahaan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="(role, idx) in rolesGuide" :key="idx" class="card p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0', role.color]">
                                <i :class="['fa-solid', role.icon]"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-surface-900 dark:text-white">{{ role.title }}</h3>
                                <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300">
                                    {{ role.scope }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-wider">Tugas & Fungsi Utama:</h4>
                            <ul class="space-y-1.5 text-xs text-surface-600 dark:text-surface-400">
                                <li v-for="(t, tIdx) in role.tasks" :key="tIdx" class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-accent-500 mt-0.5 shrink-0"></i>
                                    <span>{{ t }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-surface-100 dark:border-surface-800">
                        <p class="text-[11px] text-surface-500 dark:text-surface-400 italic">
                            <strong>Alur Kerja Singkat:</strong> {{ role.workflow }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: ALUR PROSEDUR SOP BARANG -->
        <div v-if="activeTab === 'sops'" class="space-y-8">
            <div v-for="sop in sops" :key="sop.id" class="card p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-surface-200 dark:border-surface-800 pb-4 mb-6 gap-2">
                    <h3 class="font-extrabold text-base text-surface-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-route text-primary-600"></i> {{ sop.title }}
                    </h3>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300">
                        Aktor: {{ sop.actor }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(step, sIdx) in sop.steps"
                        :key="sIdx"
                        class="p-4 rounded-xl bg-surface-50 dark:bg-surface-800/60 border border-surface-200/80 dark:border-surface-700/60 relative"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white text-xs font-extrabold flex items-center justify-center shrink-0">
                                {{ sIdx + 1 }}
                            </span>
                            <h4 class="font-bold text-xs text-surface-900 dark:text-white">{{ step.title }}</h4>
                        </div>
                        <p class="text-xs text-surface-500 dark:text-surface-400 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: KAMUS MODUL & FITUR -->
        <div v-if="activeTab === 'modules'" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="(m, mIdx) in modulesGuide" :key="mIdx" class="card p-5 hover:border-primary-400 transition-colors">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-950 text-primary-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <h3 class="font-bold text-sm text-surface-900 dark:text-white">{{ m.name }}</h3>
                    </div>
                    <p class="text-xs text-surface-500 dark:text-surface-400 leading-relaxed">{{ m.desc }}</p>
                </div>
            </div>
        </div>

        <!-- TAB 4: FAQ & PROBLEM SOLVING -->
        <div v-if="activeTab === 'faqs'" class="space-y-4 max-w-4xl">
            <div v-for="(faq, fIdx) in faqs" :key="fIdx" class="card p-5">
                <h3 class="font-bold text-sm text-surface-900 dark:text-white flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-circle-question text-warning-500"></i> {{ faq.q }}
                </h3>
                <p class="text-xs text-surface-600 dark:text-surface-300 leading-relaxed pl-6 border-l-2 border-primary-500">
                    {{ faq.a }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
