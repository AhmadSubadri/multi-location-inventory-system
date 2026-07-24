# 🌾 Agro Inventory System — Enterprise Multi-Location Stock Management

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-Modern-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PWA Ready](https://img.shields.io/badge/PWA-Ready-059669?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![Version](https://img.shields.io/badge/Version-v1.2.0_Enterprise-blue?style=for-the-badge)](https://github.com/AhmadSubadri/multi-location-inventory-system)

**Sistem Informasi Manajemen Inventory & Mutasi Stok Multi-Gudang & Multi-Toko** berbasis web yang dirancang khusus untuk distribusi dan retail sarana pertanian (pupuk, pestisida, herbisida, alat pertanian) serta siap diadaptasi untuk bisnis retail/gudang umum.

> **Supported & Developed by ASDEV Digital Solution**

---

## 🌟 Fitur Utama (Key Features)

### 🏬 1. Manajemen Multi-Lokasi Scalable
- **1 Gudang Utama (Sumber Suplai)** + **N Toko Cabang** dengan angka stok yang tercatat secara independen per lokasi.
- Penambahan lokasi baru bersifat dinamis via UI tanpa perlu mengubah struktur basis data atau kode program.

### ⏳ 2. Pelacakan Batch/Lot & Kadaluarsa (FEFO)
- Pelacakan nomor batch produksi dan tanggal kadaluarsa (*expiry date*) pada setiap barang masuk.
- Mengadopsi metode **FEFO (First Expired First Out)** otomatis pada saat transfer stok dan pencatatan penjualan.

### 🛡️ 3. Audit Trail Terpusat (Stock Ledger)
- Seluruh mutasi barang (masuk, keluar, transfer, retur, penyesuaian opname) dicatat secara **immutable** pada tabel `stock_ledgers`.
- Mencegah manipulasi angka stok dan memberikan histori mutasi kronologis yang transparan.

### 📱 4. Dukungan PWA (Progressive Web App - Install ke HP)
- PWA Manifest (`/manifest.json`) dinamis yang menyesuaikan Nama & Logo Resmi Perusahaan.
- Ikon tombol **"Install Aplikasi Mobile"** yang sangat bersih dan elegan untuk dipasang langsung ke layar utama (*Home Screen*) HP/Mobile stakeholder tanpa perlu via PlayStore.
- Offline Service Worker (`/sw.js`) untuk performa jaringan yang cepat & stabil.

### 🚚 5. Operasional Mutasi Barang Lengkap
- **Penerimaan Barang (Goods Receipt)** dari Supplier ke Gudang Utama.
- **Transfer Stok** Gudang → Toko (dan opsional Toko ↔ Toko) dengan validasi *Stok Dilarang Minus*.
- **Pencatatan Penjualan Toko** dengan pemilihan **Harga Grosir Otomatis** berdasarkan kuantitas beli.
- **Retur Barang** 3 Arah (*Customer → Toko*, *Toko → Gudang*, *Gudang → Supplier*).
- **Stok Opname** dengan penghitungan selisih otomatis & penyesuaian ledger.

### 📊 6. Laporan & Rekapitulasi Eksekutif (Exportable)
- 📈 Laporan Stok & Value Persediaan
- 🔄 Laporan Pergerakan Stok
- ⚠️ Laporan Produk Mendekati Kadaluarsa
- 💰 Laporan Penjualan Per Toko/Produk
- 📥 Laporan Penerimaan Barang Supplier
- 🚚 Laporan Transfer Stok Cabang
- ↩️ Laporan Retur Barang
- ⚡ Laporan Fast & Slow Moving Items

### 🔐 7. Otorisasi Granular & Scoping Lokasi (RBAC)
- Menggunakan `spatie/laravel-permission` untuk peran dinamis (*Super Admin, Owner, Kepala Gudang, Staff Gudang, Kepala Toko, Staff Toko*).
- Pembatasan data berbasis lokasi (*Location-scoped Isolation*) untuk staf toko & gudang.

### 🖼️ 8. Identitas Brand & Latar Belakang Login Dinamis
- Logo Perusahaan & Favicon Tab Peramban berubah secara dinamis sesuai logo yang diunggah di menu *Profil Perusahaan*.
- Pengunggahan Gambar Latar Belakang Login kustom (foto sawah, gudang, pertanian) dengan desain hero glassmorphic modern.

### 🎨 9. Dark Mode & User Guide Interaktif
- Tema **Dark / Light Mode** interaktif berbasis Tailwind CSS v4 dengan simpanan preferensi peramban.
- Halaman **Panduan & SOP Sistem** bawaan yang menyajikan petunjuk peran akun, alur operasional barang, kamus modul, dan FAQ.

---

## 🛠️ Persyaratan Sistem (Prerequisites)

- **PHP**: `^8.2` (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`)
- **Composer**: `^2.x`
- **Node.js**: `^20.x` atau `^22.x` & **npm**: `^10.x`
- **Database**: MySQL `^8.0` atau MariaDB `^10.6`

---

## 🚀 Panduan Instalasi & Konfigurasi (Setup Guide)

### 1. Clone Repository & Masuk ke Direktori
```bash
git clone https://github.com/AhmadSubadri/multi-location-inventory-system.git
cd multi-location-inventory-system
```

### 2. Install Dependensi Backend (PHP) & Frontend (Node)
```bash
composer install
npm install
```

### 3. Setup File Lingkungan (`.env`)
Salin `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_system
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key & Storage Link
```bash
php artisan key:generate
php artisan storage:link
```

### 5. Jalankan Migrasi Basis Data & Seeder Demo
```bash
php artisan migrate:fresh --seed
```

### 6. Kompilasi Aset Frontend (Vite)
```bash
npm run build
```

### 7. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi dapat diakses melalui peramban di: **`http://127.0.0.1:8000`**

---

## 🔑 Akun Demo Seeded (Default Credentials)

Password untuk seluruh akun demo di bawah ini adalah: **`password`**

| Peran (Role) | Email Login | Hak Akses Utama |
|---|---|---|
| **Super Admin** | `admin@agrosaranatani.co.id` | Akses Penuh Seluruh Sistem & Pengaturan |
| **Owner / Direktur** | `owner@agrosaranatani.co.id` | Executive View Laporan & Switch Lokasi |
| **Kepala Gudang** | `budi@agrosaranatani.co.id` | Approve Penerimaan, Transfer, & Opname Gudang |
| **Staff Gudang** | `staff_gudang@agrosaranatani.co.id` | Entri Penerimaan & Draf Transfer Gudang |
| **Kepala Toko** | `sari@agrosaranatani.co.id` | Approve Retur & Opname Toko Cabang 1 |
| **Staff Toko** | `staff_toko@agrosaranatani.co.id` | Entri Penjualan & Retur Customer Toko |

---

## 📁 Struktur Utama Proyek

```text
inventory-system/
├── app/
│   ├── Http/Controllers/        # Controllers per modul (GoodsReceipt, StockTransfer, Sales, Reports, dll)
│   ├── Http/Middleware/         # HandleInertiaRequests (Shared Props, Location Context, App Version)
│   ├── Models/                  # Eloquent Models (Product, ProductBatch, StockLedger, Location, User)
│   └── Services/                # Single Source of Truth StockLedgerService
├── database/
│   ├── migrations/              # 27 Database Migrations
│   └── seeders/                 # Database Seeder (Roles, Locations, Users, Products, Batches)
├── public/
│   ├── manifest.json            # Dynamic PWA Manifest metadata
│   └── sw.js                    # PWA Service Worker script
├── resources/
│   ├── css/app.css              # Custom Tailwind CSS v4 design tokens & Dark Mode classes
│   └── js/
│       ├── Layouts/             # AuthenticatedLayout & GuestLayout
│       └── Pages/               # Vue 3 Inertia Views (Auth, Dashboard, Master, Operations, Reports, UserGuide)
├── routes/web.php               # Registered Application & PWA Manifest Routes
└── README.md
```

---

## 📄 Lisensi & Hak Cipta

Dikembangkan untuk Perusahaan Distribusi & Retail Sarana Pertanian. Hak Cipta &copy; 2026 All Rights Reserved. **ASDEV Digital Solution**.
