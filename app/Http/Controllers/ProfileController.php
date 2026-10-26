<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman pengaturan akun (untuk semua role).
     */
    public function edit()
    {
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
     * Upload / perbarui foto profil.
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'foto_profil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'foto_profil.required' => 'Silakan pilih file foto terlebih dahulu.',
            'foto_profil.image'    => 'File harus berupa gambar.',
            'foto_profil.mimes'    => 'Format foto harus jpg, jpeg, png, atau webp.',
            'foto_profil.max'      => 'Ukuran foto maksimal 4 MB.',
        ]);

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $path = $request->file('foto_profil')->store('avatars', 'public');
        $user->update(['foto_profil' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil (kembali ke inisial nama).
     */
    public function destroyPhoto()
    {
        $user = Auth::user();
        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
            $user->update(['foto_profil' => null]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Simpan / perbarui tanda tangan digital (dari kanvas gambar atau upload file).
     */
    public function updateSignature(Request $request)
    {
        $user = Auth::user();

        // Pencegahan: Pengguna selain Superadmin tidak dapat mengubah tanda tangan jika sudah tersimpan
        if (! $user->isSuperAdmin() && $user->tanda_tangan) {
            return back()->withErrors(['signature' => 'Tanda tangan digital Anda sudah tersimpan dan terkunci. Silakan lapor ke Superadmin untuk mengajukan reset tanda tangan.']);
        }

        // 1. Upload via file gambar
        if ($request->hasFile('signature_file')) {
            $request->validate([
                'signature_file' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ], [
                'signature_file.image' => 'File harus berupa gambar tanda tangan.',
                'signature_file.mimes' => 'Format file tanda tangan harus png, jpg, jpeg, atau webp.',
                'signature_file.max'   => 'Ukuran tanda tangan maksimal 2 MB.',
            ]);

            if ($user->tanda_tangan) {
                Storage::disk('public')->delete($user->tanda_tangan);
            }

            $path = $request->file('signature_file')->store('signatures', 'public');
            $user->update(['tanda_tangan' => $path]);

            return back()->with('success', 'Tanda tangan digital berhasil disimpan dan dikunci.');
        }

        // 2. Gambar via Canvas (Base64)
        if ($request->filled('signature_data')) {
            $data = $request->input('signature_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);

                if (! in_array($type, ['jpg', 'jpeg', 'gif', 'png'], true)) {
                    return back()->withErrors(['signature' => 'Format tanda tangan tidak didukung.']);
                }

                $decoded = base64_decode($data);
                if ($decoded === false) {
                    return back()->withErrors(['signature' => 'Gagal membaca data tanda tangan digital.']);
                }

                if ($user->tanda_tangan) {
                    Storage::disk('public')->delete($user->tanda_tangan);
                }

                $filename = 'signatures/sig_' . $user->id . '_' . time() . '.' . $type;
                Storage::disk('public')->put($filename, $decoded);
                $user->update(['tanda_tangan' => $filename]);

                return back()->with('success', 'Tanda tangan digital berhasil disimpan dan dikunci.');
            }
        }

        return back()->withErrors(['signature' => 'Silakan gambar tanda tangan pada kanvas atau unggah file tanda tangan.']);
    }

    /**
     * Hapus / reset tanda tangan digital mandiri (khusus Superadmin).
     */
    public function destroySignature()
    {
        $user = Auth::user();

        // Hanya superadmin yang boleh mereset tanda tangannya sendiri secara mandiri jika sudah terkunci
        if (! $user->isSuperAdmin() && $user->tanda_tangan) {
            return back()->withErrors(['signature' => 'Tanda tangan digital Anda telah terkunci. Silakan lapor ke Superadmin untuk permohonan reset.']);
        }

        if ($user->tanda_tangan) {
            Storage::disk('public')->delete($user->tanda_tangan);
            $user->update(['tanda_tangan' => null]);
        }

        return back()->with('success', 'Tanda tangan digital berhasil di-reset.');
    }

    /**
     * Reset tanda tangan digital pengguna lain (khusus Superadmin).
     */
    public function resetUserSignature(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Akses ditolak. Hanya Superadmin yang berhak mereset tanda tangan.');

        if ($user->tanda_tangan) {
            Storage::disk('public')->delete($user->tanda_tangan);
            $user->update(['tanda_tangan' => null]);
        }

        return back()
            ->with('success', "Tanda tangan digital untuk \"{$user->name}\" (@{$user->username}) berhasil di-reset. Pengguna sekarang dapat membuat tanda tangan baru.")
            ->with('active_tab', 'signatures');
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
            'new_role'     => ['required', 'in:admin,user,teknisi,eng_admin,eng_spv,gm'],
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
            'edit_role'     => ['required', 'in:admin,user,teknisi,eng_admin,eng_spv,gm'],
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
