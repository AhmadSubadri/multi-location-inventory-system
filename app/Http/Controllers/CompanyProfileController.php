<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function index(): Response
    {
        $company = CompanyProfile::first() ?? new CompanyProfile();

        return Inertia::render('CompanyProfile/Index', [
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'default_tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code' => ['required', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $company = CompanyProfile::first();
        if (!$company) {
            $company = new CompanyProfile();
        }

        $oldValues = $company->toArray();

        if ($request->hasFile('logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $path = $request->file('logo')->store('company', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $company->fill($validated);
        $company->save();

        ActivityLog::log(
            action: 'UPDATE_COMPANY_PROFILE',
            subjectType: CompanyProfile::class,
            subjectId: $company->id,
            oldValues: $oldValues,
            newValues: $company->toArray(),
            description: "Memperbarui profil & logo perusahaan: {$company->name}"
        );

        return back()->with('success', 'Profil & Logo Perusahaan berhasil diperbarui.');
    }
}
