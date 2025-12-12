<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalFiles = File::count();
        $totalStorage = File::sum('size');  // ← GANTI dari 'file_size' ke 'size'
        $recentFiles = File::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('totalUsers', 'totalFiles', 'totalStorage', 'recentFiles'));
    }

    // All Files
    public function files()
    {
        $files = File::with('user')->latest()->get();
        return view('admin.files', compact('files'));
    }

    // Delete File (Admin)
    public function deleteFile(File $file)
    {
        Storage::disk('public')->delete($file->path);  // ← GANTI dari 'file_path' ke 'path'
        $file->delete();

        return back()->with('success', 'File deleted successfully!');
    }

    // Users Management
    public function users()
    {
        $users = User::withCount('files')->latest()->get();
        return view('admin.users', compact('users'));
    }

    // Create User
    public function createUser()
    {
        return view('admin.users-create');
    }

    // Store User
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    // Edit User
    public function editUser(User $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    // Update User
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    // Delete User
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        // Delete all user's files
        foreach ($user->files as $file) {
            Storage::disk('public')->delete($file->path);  // ← GANTI dari 'file_path' ke 'path'
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }
}