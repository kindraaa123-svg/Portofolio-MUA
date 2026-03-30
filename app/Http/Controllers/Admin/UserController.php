<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('role')->latest()->paginate(12),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        ActivityLogger::log('user', 'create', $user, ['email' => $user->email]);

        return back()->with('success', 'User baru berhasil ditambahkan.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $defaultPassword = '12345';
        $user->update(['password' => Hash::make($defaultPassword)]);

        ActivityLogger::log('user', 'reset-password', $user, [
            'email' => $user->email,
            'default_password' => $defaultPassword,
        ]);

        return back()->with('success', 'Password user berhasil direset ke 12345.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors(['user' => 'Akun yang sedang login tidak bisa dihapus.']);
        }

        $user->delete();

        ActivityLogger::log('user', 'delete', $user, ['email' => $user->email]);

        return back()->with('success', 'User berhasil dihapus.');
    }
}
