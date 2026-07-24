<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesRecordController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGuideController;
use Illuminate\Support\Facades\Route;

// Dynamic PWA Manifest Route
Route::get('/manifest.json', function () {
    $company = \App\Models\CompanyProfile::first();
    $name = $company?->name ? $company->name : 'Agro POS & Inventory';
    $shortName = $company?->name ? \Illuminate\Support\Str::limit($company->name, 15, '') : 'AgroPOS';
    $logoUrl = $company?->logo_url ? $company->logo_url : asset('favicon.ico');

    return response()->json([
        'name' => $name,
        'short_name' => $shortName,
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#0f172a',
        'theme_color' => '#0f172a',
        'orientation' => 'portrait-primary',
        'description' => "Sistem Informasi Manajemen Inventory {$name}",
        'icons' => [
            [
                'src' => $logoUrl,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $logoUrl,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ]
        ]
    ]);
});

// =====================
// AUTH ROUTES
// =====================
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/switch-location', [AuthController::class, 'switchLocation'])->name('switch-location');

    // Dashboard & Panduan Sistem
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user-guide', [UserGuideController::class, 'index'])->name('user-guide.index');

    // =====================
    // MASTER DATA MODULES
    // =====================
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);
    Route::resource('products', ProductController::class);
    Route::resource('prices', ProductPriceController::class)->except(['create', 'show', 'edit']);
    Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
    Route::resource('discounts', DiscountController::class)->except(['create', 'show', 'edit']);
    Route::resource('taxes', TaxController::class)->except(['create', 'show', 'edit']);

    // =====================
    // INVENTORY OPERATIONS
    // =====================
    Route::resource('goods-receipts', GoodsReceiptController::class);
    Route::post('goods-receipts/{goods_receipt}/approve', [GoodsReceiptController::class, 'approve'])->name('goods-receipts.approve');

    Route::resource('stock-transfers', StockTransferController::class);
    Route::post('stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve');
    Route::post('stock-transfers/{stock_transfer}/ship', [StockTransferController::class, 'ship'])->name('stock-transfers.ship');
    Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');

    Route::resource('sales-records', SalesRecordController::class);
    Route::resource('returns', ReturnController::class);
    Route::post('returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');

    Route::resource('stock-opnames', StockOpnameController::class);
    Route::post('stock-opnames/{stock_opname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');

    // =====================
    // AUDIT TRAIL / STOCK LEDGER
    // =====================
    Route::get('stock-ledgers', [StockLedgerController::class, 'index'])->name('stock-ledgers.index');

    // =====================
    // REPORTS MODULE
    // =====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('ledger', [ReportController::class, 'ledger'])->name('ledger');
        Route::get('expiry', [ReportController::class, 'expiry'])->name('expiry');
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('goods-receipts', [ReportController::class, 'goodsReceipts'])->name('goods-receipts');
        Route::get('transfers', [ReportController::class, 'transfers'])->name('transfers');
        Route::get('returns', [ReportController::class, 'returns'])->name('returns');
        Route::get('moving', [ReportController::class, 'moving'])->name('moving');
    });

    // =====================
    // SYSTEM SETTINGS & USER MANAGEMENT
    // =====================
    Route::get('company-profile', [CompanyProfileController::class, 'index'])->name('company-profile.index');
    Route::post('company-profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');

    Route::resource('locations', LocationController::class)->except(['create', 'show', 'edit']);
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);

    Route::get('audit-logs', [ActivityLogController::class, 'index'])->name('audit-logs.index');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
