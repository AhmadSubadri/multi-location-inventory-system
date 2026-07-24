<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $users = User::with(['roles', 'locations'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', 'exists:locations,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);
        if (!empty($validated['locations'])) {
            $user->locations()->attach($validated['locations']);
        }

        ActivityLog::log(
            action: 'CREATE_USER',
            subjectType: User::class,
            subjectId: $user->id,
            description: "Membuat pengguna baru: {$user->name} ({$user->email})"
        );

        return back()->with('success', 'Pengguna berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', 'exists:locations,id'],
            'is_active' => ['boolean'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $user->syncRoles([$validated['role']]);
        $user->locations()->sync($validated['locations'] ?? []);

        ActivityLog::log(
            action: 'UPDATE_USER',
            subjectType: User::class,
            subjectId: $user->id,
            description: "Mengubah data pengguna: {$user->name}"
        );

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log(
            action: 'DELETE_USER',
            subjectType: User::class,
            subjectId: $user->id,
            description: "Menghapus pengguna: {$name}"
        );

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
