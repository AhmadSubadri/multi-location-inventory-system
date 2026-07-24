<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->pluck('value', 'key')->all();

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'doc_number_reset_mode' => ['required', 'string', 'in:monthly,yearly'],
            'prefix_goods_receipt' => ['required', 'string', 'max:20'],
            'prefix_stock_transfer' => ['required', 'string', 'max:20'],
            'prefix_sales_record' => ['required', 'string', 'max:20'],
            'prefix_return' => ['required', 'string', 'max:20'],
            'prefix_stock_opname' => ['required', 'string', 'max:20'],
            'enable_store_to_store_transfer' => ['boolean'],
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
            'tax_included_in_price' => ['boolean'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'group' => 'general',
                    'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                ]
            );
        }

        ActivityLog::log(
            action: 'UPDATE_SYSTEM_SETTINGS',
            description: "Memperbarui konfigurasi sistem (Reset mode: {$validated['doc_number_reset_mode']}, Transfer toko-ke-toko: " . ($validated['enable_store_to_store_transfer'] ? 'ON' : 'OFF') . ')'
        );

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }
}
