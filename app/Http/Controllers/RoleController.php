<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        // Group permissions by module prefix (e.g. products.view -> products)
        $groupedPermissions = $permissions->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $groupedPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        ActivityLog::log(
            action: 'CREATE_ROLE',
            subjectType: Role::class,
            subjectId: $role->id,
            description: "Membuat role baru: {$role->name}"
        );

        return back()->with('success', 'Role hak akses berhasil dibuat.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        ActivityLog::log(
            action: 'UPDATE_ROLE',
            subjectType: Role::class,
            subjectId: $role->id,
            description: "Mengubah role hak akses: {$role->name}"
        );

        return back()->with('success', 'Role hak akses berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Super Admin', 'Owner', 'Kepala Gudang', 'Kepala Toko', 'Staff Gudang', 'Staff Toko'])) {
            return back()->with('error', 'Role standar sistem tidak boleh dihapus.');
        }

        $name = $role->name;
        $role->delete();

        ActivityLog::log(
            action: 'DELETE_ROLE',
            subjectType: Role::class,
            subjectId: $role->id,
            description: "Menghapus role: {$name}"
        );

        return back()->with('success', 'Role berhasil dihapus.');
    }
}
