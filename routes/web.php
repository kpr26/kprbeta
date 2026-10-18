<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogsheetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── PWA & Offline Routes ─────────────────────────────
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/icons/icon-{size}.png', [PwaController::class, 'icon'])->where('size', '192|512')->name('pwa.icon');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

// ── Storage Asset Delivery (Shared Hosting tanpa Symlink) ──
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (! file_exists($filePath)) {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200"><rect width="200" height="200" fill="#0f172a" rx="12"/><circle cx="70" cy="70" r="16" fill="#334155"/><path d="M40 150h120l-37.5-50-25 33.3-17.5-23.3L40 150z" fill="#475569"/><text x="100" y="175" fill="#94a3b8" font-size="11" font-family="sans-serif" text-anchor="middle">Foto Tidak Ditemukan</text></svg>';
        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-cache',
        ]);
    }

    $mime = mime_content_type($filePath) ?: 'image/jpeg';
    return response()->file($filePath, [
        'Content-Type'  => $mime,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.fallback');

// Root route: tampilkan form login jika guest (200 OK untuk PWA & crawler), atau redirect ke dashboard jika sudah login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');


// ── Auth Routes ──────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// ── Protected Routes ─────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── Pengaturan Akun ──
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil/info', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::patch('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/akun-baru', [ProfileController::class, 'storeUser'])->name('profile.storeUser');
    Route::put('/profil/user/{user}', [ProfileController::class, 'updateUser'])->name('profile.updateUser');
    Route::patch('/profil/user/{user}/toggle-status', [ProfileController::class, 'toggleUserStatus'])->name('profile.toggleUserStatus');
    Route::delete('/profil/user/{user}', [ProfileController::class, 'destroyUser'])->name('profile.destroyUser');

    // ── Helper Maintenance / Migrasi Database (Khusus Admin) ──
    Route::middleware('role:admin')->get('/admin/migrate-db', function () {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        return redirect()->route('dashboard')->with('success', 'Migrasi database berhasil dijalankan: ' . ($output ?: 'Database sudah versi terbaru.'));
    })->name('admin.migrate');

    Route::middleware('role:admin')->get('/admin/storage-link', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return redirect()->route('dashboard')->with('success', 'Storage link berhasil: ' . ($output ?: 'storage:link selesai.'));
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal membuat storage link: ' . $e->getMessage());
        }
    })->name('admin.storage.link');


    // ── Logsheet Listrik & Air ──
    Route::middleware('role:admin,eng_admin,eng_spv')->group(function () {
        Route::get('/logsheet/export/excel', [LogsheetController::class, 'exportExcel'])->name('logsheet.excel');
        Route::get('/logsheet/export/pdf', [LogsheetController::class, 'exportPdf'])->name('logsheet.pdf');
        Route::post('/logsheet/{logsheet}/verify', [LogsheetController::class, 'verify'])->name('logsheet.verify');
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('/logsheet/{logsheet}', [LogsheetController::class, 'destroy'])->name('logsheet.destroy');
    });
    // ── Logsheet Input Terpisah (Listrik & Air) & Hub Card Menu ──
    Route::get('/logsheet/hub', [LogsheetController::class, 'hub'])->name('logsheet.hub');
    Route::get('/logsheet/input/listrik', [LogsheetController::class, 'createListrik'])->name('logsheet.create.listrik');
    Route::post('/logsheet/input/listrik', [LogsheetController::class, 'storeListrik'])->name('logsheet.store.listrik');
    Route::get('/logsheet/input/air', [LogsheetController::class, 'createAir'])->name('logsheet.create.air');
    Route::post('/logsheet/input/air', [LogsheetController::class, 'storeAir'])->name('logsheet.store.air');

    Route::resource('logsheet', LogsheetController::class)->except('destroy');
});
