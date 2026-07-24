<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductStockSetting;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ========== UNITS ==========
        $botol = Unit::create(['name' => 'Botol', 'symbol' => 'btl']);
        $sachet = Unit::create(['name' => 'Sachet', 'symbol' => 'sct']);
        $dus = Unit::create(['name' => 'Dus', 'symbol' => 'dus']);
        $kg = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg']);
        $liter = Unit::create(['name' => 'Liter', 'symbol' => 'ltr']);
        $pcs = Unit::create(['name' => 'Pcs', 'symbol' => 'pcs']);
        $karung = Unit::create(['name' => 'Karung', 'symbol' => 'krg']);
        $unit = Unit::create(['name' => 'Unit', 'symbol' => 'unit']);

        // ========== TAX ==========
        Tax::create(['name' => 'PPN 11%', 'percent' => 11.00, 'is_default' => true]);

        // ========== CATEGORIES (Hierarkis) ==========
        $pestisida = ProductCategory::create(['name' => 'Pestisida']);
        $insektisida = ProductCategory::create(['name' => 'Insektisida', 'parent_id' => $pestisida->id]);
        $herbisida = ProductCategory::create(['name' => 'Herbisida', 'parent_id' => $pestisida->id]);
        $fungisida = ProductCategory::create(['name' => 'Fungisida', 'parent_id' => $pestisida->id]);
        $pupuk = ProductCategory::create(['name' => 'Pupuk']);
        $pupukKimia = ProductCategory::create(['name' => 'Pupuk Kimia', 'parent_id' => $pupuk->id]);
        $pupukOrganik = ProductCategory::create(['name' => 'Pupuk Organik', 'parent_id' => $pupuk->id]);
        $alatPertanian = ProductCategory::create(['name' => 'Alat Pertanian']);
        $bibit = ProductCategory::create(['name' => 'Benih / Bibit']);

        // ========== SUPPLIERS ==========
        $supplier1 = Supplier::create([
            'name' => 'PT Syngenta Indonesia',
            'contact_person' => 'Robert Tan',
            'phone' => '021-7654321',
            'email' => 'sales@syngenta.co.id',
            'address' => 'Jakarta Selatan',
        ]);

        $supplier2 = Supplier::create([
            'name' => 'PT Bayer CropScience',
            'contact_person' => 'Diana Putri',
            'phone' => '021-8765432',
            'email' => 'order@bayer.co.id',
            'address' => 'Tangerang, Banten',
        ]);

        $supplier3 = Supplier::create([
            'name' => 'PT Petrokimia Gresik',
            'contact_person' => 'Hasan Basri',
            'phone' => '031-3981811',
            'email' => 'marketing@petrokimia-gresik.com',
            'address' => 'Gresik, Jawa Timur',
        ]);

        $supplier4 = Supplier::create([
            'name' => 'CV Agro Makmur Jaya',
            'contact_person' => 'Pak Eko',
            'phone' => '0260-987654',
            'address' => 'Subang, Jawa Barat',
        ]);

        // ========== PRODUCTS ==========
        $gudang = Location::where('type', 'warehouse')->first();
        $toko1 = Location::where('code', 'TKO-01')->first();
        $toko2 = Location::where('code', 'TKO-02')->first();
        $toko3 = Location::where('code', 'TKO-03')->first();

        $products = [
            [
                'sku' => 'INS-001',
                'name' => 'Prevathon 50 SC',
                'category_id' => $insektisida->id,
                'base_unit_id' => $botol->id,
                'brand' => 'Dupont/FMC',
                'active_ingredient' => 'Chlorantraniliprole 50 g/L',
                'registration_number' => 'RI. 01010120185001',
                'is_hazardous' => true,
                'hazardous_notes' => 'Simpan di tempat sejuk, jauh dari makanan dan minuman.',
                'prices' => [
                    ['type' => 'purchase', 'price' => 85000],
                    ['type' => 'retail', 'price' => 125000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 110000, 'min_qty' => 12],
                ],
                'conversions' => [['from' => $dus, 'to' => $botol, 'factor' => 20]],
                'stock' => ['gudang' => 200, 'toko1' => 50, 'toko2' => 30, 'toko3' => 25],
                'batch' => 'B2026-001',
                'expiry' => '2028-06-30',
                'suppliers' => [$supplier1->id],
            ],
            [
                'sku' => 'HRB-001',
                'name' => 'Gramoxone 276 SL',
                'category_id' => $herbisida->id,
                'base_unit_id' => $liter->id,
                'brand' => 'Syngenta',
                'active_ingredient' => 'Paraquat diklorida 276 g/L',
                'registration_number' => 'RI. 01020120091234',
                'is_hazardous' => true,
                'hazardous_notes' => 'B3 - Sangat beracun. Wajib APD saat handling.',
                'prices' => [
                    ['type' => 'purchase', 'price' => 65000],
                    ['type' => 'retail', 'price' => 95000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 82000, 'min_qty' => 24],
                ],
                'stock' => ['gudang' => 300, 'toko1' => 60, 'toko2' => 45, 'toko3' => 40],
                'batch' => 'B2026-002',
                'expiry' => '2028-12-31',
                'suppliers' => [$supplier1->id],
            ],
            [
                'sku' => 'HRB-002',
                'name' => 'Roundup 486 SL',
                'category_id' => $herbisida->id,
                'base_unit_id' => $liter->id,
                'brand' => 'Bayer/Monsanto',
                'active_ingredient' => 'Isopropilamin glifosat 486 g/L',
                'registration_number' => 'RI. 01020120063456',
                'is_hazardous' => true,
                'prices' => [
                    ['type' => 'purchase', 'price' => 72000],
                    ['type' => 'retail', 'price' => 105000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 90000, 'min_qty' => 12],
                ],
                'stock' => ['gudang' => 250, 'toko1' => 40, 'toko2' => 35, 'toko3' => 30],
                'batch' => 'B2026-003',
                'expiry' => '2029-03-31',
                'suppliers' => [$supplier2->id],
            ],
            [
                'sku' => 'FNG-001',
                'name' => 'Antracol 70 WP',
                'category_id' => $fungisida->id,
                'base_unit_id' => $kg->id,
                'brand' => 'Bayer',
                'active_ingredient' => 'Propineb 70%',
                'is_hazardous' => true,
                'prices' => [
                    ['type' => 'purchase', 'price' => 95000],
                    ['type' => 'retail', 'price' => 140000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 120000, 'min_qty' => 10],
                ],
                'stock' => ['gudang' => 150, 'toko1' => 30, 'toko2' => 25, 'toko3' => 20],
                'batch' => 'B2026-004',
                'expiry' => '2028-09-30',
                'suppliers' => [$supplier2->id],
            ],
            [
                'sku' => 'PUP-001',
                'name' => 'Pupuk Urea Subsidi',
                'category_id' => $pupukKimia->id,
                'base_unit_id' => $karung->id,
                'brand' => 'Petrokimia Gresik',
                'active_ingredient' => 'N 46%',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 90000],
                    ['type' => 'retail', 'price' => 112500, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 105000, 'min_qty' => 50],
                ],
                'conversions' => [['from' => $karung, 'to' => $kg, 'factor' => 50]],
                'stock' => ['gudang' => 500, 'toko1' => 100, 'toko2' => 80, 'toko3' => 70],
                'batch' => 'B2026-005',
                'expiry' => null,
                'suppliers' => [$supplier3->id],
            ],
            [
                'sku' => 'PUP-002',
                'name' => 'Pupuk NPK Phonska',
                'category_id' => $pupukKimia->id,
                'base_unit_id' => $karung->id,
                'brand' => 'Petrokimia Gresik',
                'active_ingredient' => 'NPK 15-15-15',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 115000],
                    ['type' => 'retail', 'price' => 150000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 135000, 'min_qty' => 50],
                ],
                'conversions' => [['from' => $karung, 'to' => $kg, 'factor' => 50]],
                'stock' => ['gudang' => 400, 'toko1' => 80, 'toko2' => 60, 'toko3' => 55],
                'batch' => 'B2026-006',
                'expiry' => null,
                'suppliers' => [$supplier3->id],
            ],
            [
                'sku' => 'PUP-003',
                'name' => 'Pupuk Organik Petroganik',
                'category_id' => $pupukOrganik->id,
                'base_unit_id' => $karung->id,
                'brand' => 'Petrokimia Gresik',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 20000],
                    ['type' => 'retail', 'price' => 30000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 25000, 'min_qty' => 100],
                ],
                'stock' => ['gudang' => 300, 'toko1' => 50, 'toko2' => 40, 'toko3' => 35],
                'batch' => 'B2026-007',
                'expiry' => null,
                'suppliers' => [$supplier3->id],
            ],
            [
                'sku' => 'ALT-001',
                'name' => 'Sprayer Knapsack Manual 16L',
                'category_id' => $alatPertanian->id,
                'base_unit_id' => $unit->id,
                'brand' => 'Swan',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 185000],
                    ['type' => 'retail', 'price' => 275000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 250000, 'min_qty' => 5],
                ],
                'stock' => ['gudang' => 30, 'toko1' => 5, 'toko2' => 4, 'toko3' => 3],
                'batch' => 'B2026-008',
                'expiry' => null,
                'suppliers' => [$supplier4->id],
            ],
            [
                'sku' => 'ALT-002',
                'name' => 'Cangkul Baja Cap Garuda',
                'category_id' => $alatPertanian->id,
                'base_unit_id' => $pcs->id,
                'brand' => 'Garuda',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 45000],
                    ['type' => 'retail', 'price' => 75000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 65000, 'min_qty' => 10],
                ],
                'stock' => ['gudang' => 50, 'toko1' => 10, 'toko2' => 8, 'toko3' => 6],
                'batch' => 'B2026-009',
                'expiry' => null,
                'suppliers' => [$supplier4->id],
            ],
            [
                'sku' => 'INS-002',
                'name' => 'Virtako 300 SC',
                'category_id' => $insektisida->id,
                'base_unit_id' => $botol->id,
                'brand' => 'Syngenta',
                'active_ingredient' => 'Thiamethoxam + Chlorantraniliprole',
                'registration_number' => 'RI. 01010120155678',
                'is_hazardous' => true,
                'prices' => [
                    ['type' => 'purchase', 'price' => 135000],
                    ['type' => 'retail', 'price' => 195000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 175000, 'min_qty' => 12],
                ],
                'conversions' => [['from' => $dus, 'to' => $botol, 'factor' => 12]],
                'stock' => ['gudang' => 120, 'toko1' => 24, 'toko2' => 18, 'toko3' => 15],
                'batch' => 'B2026-010',
                'expiry' => '2027-12-31',
                'suppliers' => [$supplier1->id],
            ],
            [
                'sku' => 'BNH-001',
                'name' => 'Benih Padi Ciherang',
                'category_id' => $bibit->id,
                'base_unit_id' => $kg->id,
                'brand' => 'Sang Hyang Seri',
                'is_hazardous' => false,
                'prices' => [
                    ['type' => 'purchase', 'price' => 12000],
                    ['type' => 'retail', 'price' => 18000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 15000, 'min_qty' => 100],
                ],
                'stock' => ['gudang' => 500, 'toko1' => 100, 'toko2' => 80, 'toko3' => 60],
                'batch' => 'B2026-011',
                'expiry' => '2027-06-30',
                'suppliers' => [$supplier4->id],
            ],
            [
                'sku' => 'FNG-002',
                'name' => 'Score 250 EC',
                'category_id' => $fungisida->id,
                'base_unit_id' => $botol->id,
                'brand' => 'Syngenta',
                'active_ingredient' => 'Difenokonazol 250 g/L',
                'is_hazardous' => true,
                'prices' => [
                    ['type' => 'purchase', 'price' => 110000],
                    ['type' => 'retail', 'price' => 165000, 'min_qty' => 1],
                    ['type' => 'wholesale', 'price' => 145000, 'min_qty' => 12],
                ],
                'stock' => ['gudang' => 100, 'toko1' => 20, 'toko2' => 15, 'toko3' => 12],
                'batch' => 'B2026-012',
                'expiry' => '2028-03-31',
                'suppliers' => [$supplier1->id],
            ],
        ];

        foreach ($products as $pData) {
            $product = Product::create([
                'sku' => $pData['sku'],
                'name' => $pData['name'],
                'category_id' => $pData['category_id'],
                'base_unit_id' => $pData['base_unit_id'],
                'brand' => $pData['brand'] ?? null,
                'active_ingredient' => $pData['active_ingredient'] ?? null,
                'registration_number' => $pData['registration_number'] ?? null,
                'is_hazardous' => $pData['is_hazardous'] ?? false,
                'hazardous_notes' => $pData['hazardous_notes'] ?? null,
            ]);

            // Prices
            foreach ($pData['prices'] as $price) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price_type' => $price['type'],
                    'min_qty' => $price['min_qty'] ?? 1,
                    'price' => $price['price'],
                ]);
            }

            // Unit conversions
            if (isset($pData['conversions'])) {
                foreach ($pData['conversions'] as $conv) {
                    UnitConversion::create([
                        'product_id' => $product->id,
                        'from_unit_id' => $conv['from']->id,
                        'to_unit_id' => $conv['to']->id,
                        'conversion_factor' => $conv['factor'],
                    ]);
                }
            }

            // Stock settings (min stock per location)
            $locations = [$gudang, $toko1, $toko2, $toko3];
            $minStocks = [20, 5, 5, 5]; // default minimums
            foreach ($locations as $i => $loc) {
                ProductStockSetting::create([
                    'product_id' => $product->id,
                    'location_id' => $loc->id,
                    'min_stock' => $minStocks[$i],
                ]);
            }

            // Create batches with initial stock
            $stockMap = [
                'gudang' => $gudang,
                'toko1' => $toko1,
                'toko2' => $toko2,
                'toko3' => $toko3,
            ];

            foreach ($pData['stock'] as $key => $qty) {
                ProductBatch::create([
                    'product_id' => $product->id,
                    'location_id' => $stockMap[$key]->id,
                    'batch_number' => $pData['batch'],
                    'production_date' => now()->subMonths(2),
                    'expiry_date' => $pData['expiry'] ? $pData['expiry'] : null,
                    'initial_qty' => $qty,
                    'remaining_qty' => $qty,
                ]);
            }

            // Supplier relationship
            if (isset($pData['suppliers'])) {
                $product->suppliers()->attach($pData['suppliers']);
            }
        }
    }
}
