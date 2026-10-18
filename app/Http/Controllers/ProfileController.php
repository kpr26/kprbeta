<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman pengaturan akun.
     */
    public function edit()
    {
        // Role teknisi hanya boleh mengakses menu logsheet
        if (Auth::user()->role === 'teknisi') {
            return redirect()->route('logsheet.create');
        }

        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update informasi profil (nama, username, email).
     */
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai pengguna lain.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah dipakai pengguna lain.',
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }

    /**
     * Update password pengguna.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
            'password.min'              => 'Password minimal 8 karakter.',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Buat akun pengguna baru (admin only).
     */
    public function storeUser(Request $request)
    {
        // Hanya admin yang boleh membuat akun baru
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'new_name'     => ['required', 'string', 'max:100'],
            'new_username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'new_email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'new_role'     => ['required', 'in:admin,user,teknisi,eng_admin,eng_spv'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'new_name.required'     => 'Nama lengkap wajib diisi.',
            'new_username.required' => 'Username wajib diisi.',
            'new_username.unique'   => 'Username sudah digunakan.',
            'new_email.required'    => 'Email wajib diisi.',
            'new_email.email'       => 'Format email tidak valid.',
            'new_email.unique'      => 'Email sudah digunakan.',
            'new_role.required'     => 'Role wajib dipilih.',
            'new_role.in'           => 'Role tidak valid.',
            'new_password.required' => 'Password wajib diisi.',
            'new_password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'new_password.min'      => 'Password minimal 8 karakter.',
        ]);

        User::create([
            'name'     => $request->new_name,
            'username' => $request->new_username,
            'email'    => $request->new_email,
            'role'     => $request->new_role,
            'password' => Hash::make($request->new_password),
        ]);

        return back()
            ->with('success', "Akun untuk \"{$request->new_name}\" berhasil dibuat.")
            ->with('active_tab', 'users');
    }

    /**
     * Update akun pengguna oleh admin (Nama, Username, Email, Role, Status, Password opsional).
     */
    public function updateUser(Request $request, User $user)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'edit_name'     => ['required', 'string', 'max:100'],
            'edit_username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'edit_email'    => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'edit_role'     => ['required', 'in:admin,user,teknisi,eng_admin,eng_spv'],
            'edit_is_active'=> ['required', 'in:0,1'],
            'edit_password' => ['nullable', 'string', 'min:8'],
        ], [
            'edit_name.required'     => 'Nama lengkap wajib diisi.',
            'edit_username.required' => 'Username wajib diisi.',
            'edit_username.unique'   => 'Username sudah digunakan oleh akun lain.',
            'edit_email.required'    => 'Email wajib diisi.',
            'edit_email.email'       => 'Format email tidak valid.',
            'edit_email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'edit_role.required'     => 'Role wajib dipilih.',
            'edit_password.min'      => 'Password baru minimal 8 karakter.',
        ]);

        // Pencegahan agar admin tidak mendowngrade / menonaktifkan dirinya sendiri secara tidak sengaja
        if ($user->id === Auth::id()) {
            if ($request->edit_role !== 'admin') {
                return back()
                    ->withErrors(['edit_role' => 'Anda tidak dapat mengubah role akun Anda sendiri.'])
                    ->with('active_tab', 'users');
            }
            if ($request->edit_is_active == '0') {
                return back()
                    ->withErrors(['edit_is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'])
                    ->with('active_tab', 'users');
            }
        }

        $data = [
            'name'      => $request->edit_name,
            'username'  => $request->edit_username,
            'email'     => $request->edit_email,
            'role'      => $request->edit_role,
            'is_active' => (bool) $request->edit_is_active,
        ];

        if ($request->filled('edit_password')) {
            $data['password'] = Hash::make($request->edit_password);
        }

        $user->update($data);

        return back()
            ->with('success', "Akun \"{$user->name}\" berhasil diperbarui.")
            ->with('active_tab', 'users');
    }

    /**
     * Toggle status aktif/nonaktif akun pengguna (admin only).
     */
    public function toggleUserStatus(User $user)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($user->id === Auth::id()) {
            return back()
                ->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'])
                ->with('active_tab', 'users');
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()
            ->with('success', "Akun \"{$user->name}\" berhasil {$statusText}.")
            ->with('active_tab', 'users');
    }

    /**
     * Hapus akun pengguna secara permanen (admin only).
     */
    public function destroyUser(User $user)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        // Pencegahan agar admin tidak menghapus akunnya sendiri
        if ($user->id === Auth::id()) {
            return back()
                ->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.'])
                ->with('active_tab', 'users');
        }

        $userName = $user->name;
        $userUsername = $user->username;

        // Amankan data historis meter readings jika pernah dicatat oleh user ini
        // Reassign ke admin saat ini agar riwayat pemakaian listrik & air gedung tidak hilang terhapus
        \App\Models\MeterReading::where('user_id', $user->id)
            ->update(['user_id' => Auth::id()]);

        \App\Models\MeterReading::where('verified_by', $user->id)
            ->update(['verified_by' => null]);

        $user->delete();

        return back()
            ->with('success', "Akun \"{$userName}\" (@{$userUsername}) berhasil dihapus selamanya.")
            ->with('active_tab', 'users');
    }
}
