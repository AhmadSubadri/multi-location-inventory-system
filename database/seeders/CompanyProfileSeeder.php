<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::create([
            'name' => 'PT Agro Sarana Tani',
            'tagline' => 'Solusi Lengkap Sarana Pertanian',
            'address' => 'Jl. Raya Pertanian No. 123, Kecamatan Sukamaju, Kabupaten Subang, Jawa Barat 41211',
            'phone' => '0260-123456',
            'email' => 'info@agrosaranatani.co.id',
            'npwp' => '12.345.678.9-012.000',
            'default_tax_percent' => 11.00,
            'currency_symbol' => 'Rp',
            'currency_code' => 'IDR',
        ]);

        // Document numbering settings
        $settings = [
            // Document numbering
            ['key' => 'doc_number_reset_mode', 'value' => 'monthly', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Mode Reset Nomor Dokumen', 'description' => 'Reset counter nomor dokumen per bulan (monthly) atau per tahun (yearly)'],
            ['key' => 'prefix_goods_receipt', 'value' => 'GR', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Prefix Penerimaan Barang'],
            ['key' => 'prefix_stock_transfer', 'value' => 'TRF', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Prefix Transfer Stok'],
            ['key' => 'prefix_sales_record', 'value' => 'SLS', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Prefix Pencatatan Penjualan'],
            ['key' => 'prefix_return', 'value' => 'RTN', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Prefix Retur'],
            ['key' => 'prefix_stock_opname', 'value' => 'ADJ', 'group' => 'document_numbering', 'type' => 'string', 'label' => 'Prefix Stok Opname'],

            // Feature toggles
            ['key' => 'enable_store_to_store_transfer', 'value' => '0', 'group' => 'features', 'type' => 'boolean', 'label' => 'Aktifkan Transfer Antar Toko', 'description' => 'Jika diaktifkan, toko dapat mengirim stok ke toko lain tanpa melalui gudang'],
            ['key' => 'expiry_warning_days', 'value' => '30', 'group' => 'notifications', 'type' => 'integer', 'label' => 'Peringatan Kadaluarsa (Hari)', 'description' => 'Berapa hari sebelum kadaluarsa produk muncul di notifikasi'],

            // Tax defaults
            ['key' => 'tax_included_in_price', 'value' => '1', 'group' => 'tax', 'type' => 'boolean', 'label' => 'Harga Sudah Termasuk Pajak'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
