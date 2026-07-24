<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define modules and their actions
        $modules = [
            'company_settings' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'locations' => ['view', 'create', 'edit', 'delete'],
            'products' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'units' => ['view', 'create', 'edit', 'delete'],
            'suppliers' => ['view', 'create', 'edit', 'delete'],
            'prices' => ['view', 'create', 'edit', 'delete'],
            'discounts' => ['view', 'create', 'edit', 'delete'],
            'taxes' => ['view', 'create', 'edit', 'delete'],
            'goods_receipts' => ['view', 'create', 'edit', 'delete', 'approve'],
            'stock_transfers' => ['view', 'create', 'edit', 'delete', 'approve', 'ship', 'receive'],
            'sales_records' => ['view', 'create', 'edit', 'delete'],
            'returns' => ['view', 'create', 'edit', 'delete', 'approve'],
            'stock_opnames' => ['view', 'create', 'edit', 'delete', 'approve'],
            'stock_ledgers' => ['view'],
            'reports' => ['view', 'export'],
            'audit_logs' => ['view'],
            'dashboard' => ['view_all', 'view_own_location'],
            'notifications' => ['view'],
        ];

        // Create all permissions
        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $allPermissions[] = $permissionName;
            }
        }

        // =====================
        // ROLES & ASSIGNMENTS
        // =====================

        // Super Admin — gets ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        // Owner / Direktur — view everything, no operational CRUD
        $owner = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $ownerPermissions = collect($allPermissions)->filter(function ($p) {
            return str_contains($p, '.view') || str_contains($p, '.export') ||
                   $p === 'dashboard.view_all' || $p === 'audit_logs.view';
        })->toArray();
        $owner->syncPermissions($ownerPermissions);

        // Kepala Gudang
        $kepalaGudang = Role::firstOrCreate(['name' => 'Kepala Gudang', 'guard_name' => 'web']);
        $kepalaGudang->syncPermissions([
            'products.view', 'categories.view', 'units.view', 'suppliers.view',
            'prices.view', 'locations.view',
            'goods_receipts.view', 'goods_receipts.approve',
            'stock_transfers.view', 'stock_transfers.approve', 'stock_transfers.ship',
            'returns.view', 'returns.approve',
            'stock_opnames.view', 'stock_opnames.approve',
            'stock_ledgers.view',
            'reports.view', 'reports.export',
            'dashboard.view_own_location',
            'notifications.view',
        ]);

        // Staff Gudang
        $staffGudang = Role::firstOrCreate(['name' => 'Staff Gudang', 'guard_name' => 'web']);
        $staffGudang->syncPermissions([
            'products.view', 'categories.view', 'units.view', 'suppliers.view',
            'prices.view', 'locations.view',
            'goods_receipts.view', 'goods_receipts.create', 'goods_receipts.edit',
            'stock_transfers.view', 'stock_transfers.create', 'stock_transfers.edit', 'stock_transfers.ship',
            'stock_opnames.view', 'stock_opnames.create', 'stock_opnames.edit',
            'stock_ledgers.view',
            'reports.view',
            'dashboard.view_own_location',
            'notifications.view',
        ]);

        // Kepala Toko
        $kepalaToko = Role::firstOrCreate(['name' => 'Kepala Toko', 'guard_name' => 'web']);
        $kepalaToko->syncPermissions([
            'products.view', 'categories.view', 'units.view', 'prices.view',
            'locations.view',
            'stock_transfers.view', 'stock_transfers.receive',
            'sales_records.view',
            'returns.view', 'returns.approve',
            'stock_opnames.view', 'stock_opnames.approve',
            'stock_ledgers.view',
            'reports.view', 'reports.export',
            'dashboard.view_own_location',
            'notifications.view',
        ]);

        // Staff Toko
        $staffToko = Role::firstOrCreate(['name' => 'Staff Toko', 'guard_name' => 'web']);
        $staffToko->syncPermissions([
            'products.view', 'categories.view', 'units.view', 'prices.view',
            'locations.view',
            'stock_transfers.view', 'stock_transfers.receive',
            'sales_records.view', 'sales_records.create', 'sales_records.edit',
            'returns.view', 'returns.create', 'returns.edit',
            'stock_opnames.view', 'stock_opnames.create', 'stock_opnames.edit',
            'stock_ledgers.view',
            'reports.view',
            'dashboard.view_own_location',
            'notifications.view',
        ]);

        // Optional roles (created but with minimal permissions for now)
        $purchasing = Role::firstOrCreate(['name' => 'Purchasing', 'guard_name' => 'web']);
        $purchasing->syncPermissions([
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'products.view', 'prices.view',
            'goods_receipts.view',
            'dashboard.view_own_location',
        ]);

        $finance = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            'products.view', 'prices.view',
            'reports.view', 'reports.export',
            'stock_ledgers.view',
            'dashboard.view_all',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions(
            collect($allPermissions)->filter(fn($p) =>
                str_contains($p, '.view') || str_contains($p, '.export')
            )->toArray()
        );
    }
}
