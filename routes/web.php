<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyLogBookController;
use App\Http\Controllers\LogsheetController;
use App\Http\Controllers\PublicAreaLogsheetController;
use App\Http\Controllers\PublicAreaLvsbController;
use App\Http\Controllers\PublicAreaMvsbController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GmMonitoringController;
use App\Http\Controllers\WhatsappSettingController;
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
Route::get('/.well-known/assetlinks.json', function () {
    $path = public_path('.well-known/assetlinks.json');
    if (file_exists($path)) {
        return response(file_get_contents($path), 200, [
            'Content-Type'                => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
    return response()->json([], 200, ['Content-Type' => 'application/json']);
});

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
    Route::middleware('role:gm,admin,superadmin,eng_spv,eng_admin')->group(function () {
        Route::get('/gm/monitoring', [GmMonitoringController::class, 'index'])->name('gm.monitoring');
    });

    // ── Pengaturan Notifikasi WhatsApp Otomatis (Khusus Super Admin) ──
    Route::middleware('role:superadmin,admin')->prefix('admin/whatsapp')->name('admin.whatsapp.')->group(function () {
        Route::get('/settings', [WhatsappSettingController::class, 'index'])->name('settings');
        Route::put('/settings', [WhatsappSettingController::class, 'update'])->name('update');
        Route::get('/preview', [WhatsappSettingController::class, 'preview'])->name('preview');
        Route::post('/test-send', [WhatsappSettingController::class, 'sendTest'])->name('test-send');
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── Pengaturan Akun ──
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil/info', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::patch('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profil/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::post('/profil/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');
    Route::delete('/profil/signature', [ProfileController::class, 'destroySignature'])->name('profile.signature.destroy');
    Route::post('/profil/akun-baru', [ProfileController::class, 'storeUser'])->name('profile.storeUser');
    Route::put('/profil/user/{user}', [ProfileController::class, 'updateUser'])->name('profile.updateUser');
    Route::patch('/profil/user/{user}/toggle-status', [ProfileController::class, 'toggleUserStatus'])->name('profile.toggleUserStatus');
    Route::delete('/profil/user/{user}', [ProfileController::class, 'destroyUser'])->name('profile.destroyUser');
    Route::post('/profil/signatures/{user}/reset', [ProfileController::class, 'resetUserSignature'])->name('admin.signatures.reset');

    // ── Helper Maintenance / Migrasi Database (Khusus Admin) ──
    Route::middleware('role:admin')->get('/admin/migrate-db', function () {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return redirect()->route('dashboard')->with('success', 'Migrasi database & clear cache berhasil: ' . ($output ?: 'Database sudah versi terbaru.'));
    })->name('admin.migrate');

    Route::middleware('role:admin')->get('/admin/clear-cache', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return redirect()->route('dashboard')->with('success', 'Seluruh cache aplikasi (view, route, config) berhasil dibersihkan!');
    })->name('admin.clear.cache');

    Route::middleware('role:admin')->get('/admin/storage-link', function () {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (! is_dir($target)) {
            @mkdir($target, 0755, true);
        }

        // Cek jika symlink atau folder storage sudah ada
        if (is_link($link) || (file_exists($link) && is_dir($link))) {
            return redirect()->route('dashboard')->with('success', 'Storage link sudah aktif di server.');
        }

        // Coba buat symlink secara aman jika fungsi symlink diizinkan hosting
        if (function_exists('symlink')) {
            try {
                if (@symlink($target, $link)) {
                    return redirect()->route('dashboard')->with('success', 'Storage link berhasil dibuat via symlink.');
                }
            } catch (\Throwable $e) {
                // Lanjut ke fallback route
            }
        }

        // Jika fungsi symlink & exec dimatikan oleh hosting, route storage fallback otomatis melayani file
        return redirect()->route('dashboard')->with('success', 'Storage aktif! Foto lampiran dilayani secara otomatis melalui storage route delivery.');
    })->name('admin.storage.link');


    // ── Logsheet Listrik & Air ──
    Route::middleware('role:admin,superadmin,eng_admin,eng_spv,gm')->group(function () {
        Route::get('/logsheet/export/excel', [LogsheetController::class, 'exportExcel'])->name('logsheet.excel');
        Route::get('/logsheet/export/pdf', [LogsheetController::class, 'exportPdf'])->name('logsheet.pdf');
    });
    Route::middleware('role:admin,superadmin,eng_admin,eng_spv')->group(function () {
        Route::post('/logsheet/{logsheet}/verify', [LogsheetController::class, 'verify'])->name('logsheet.verify')->whereNumber('logsheet');
    });
    Route::middleware('role:admin,superadmin,eng_admin,eng_spv')->group(function () {
        Route::delete('/logsheet/{logsheet}', [LogsheetController::class, 'destroy'])->name('logsheet.destroy')->whereNumber('logsheet');
    });
    // ── Logsheet Input Terpisah (Listrik & Air) & Hub Card Menu ──
    Route::get('/logsheet/hub', [LogsheetController::class, 'hub'])->name('logsheet.hub');
    Route::get('/logsheet/input/listrik', [LogsheetController::class, 'createListrik'])->name('logsheet.create.listrik');
    Route::post('/logsheet/input/listrik', [LogsheetController::class, 'storeListrik'])->name('logsheet.store.listrik');
    Route::get('/logsheet/input/air', [LogsheetController::class, 'createAir'])->name('logsheet.create.air');
    Route::post('/logsheet/input/air', [LogsheetController::class, 'storeAir'])->name('logsheet.store.air');

    // ── Input Manual Logsheet Listrik & Air (Khusus Super Admin untuk Tanggal Terlewat) ──
    Route::middleware('role:admin,superadmin')->group(function () {
        // Aksi Masal Logsheet (Verifikasi Masal & Hapus Masal Maksimal 1 Bulan)
        Route::post('/logsheet/bulk-verify', [LogsheetController::class, 'bulkVerify'])->name('logsheet.bulk.verify');
        Route::post('/logsheet/bulk-destroy', [LogsheetController::class, 'bulkDestroy'])->name('logsheet.bulk.destroy');

        // Bulk Input Manual Air (Water Meter Gedung - File Excel)
        Route::get('/logsheet/bulk/air', [LogsheetController::class, 'createBulkAir'])->name('logsheet.bulk.air');
        Route::post('/logsheet/bulk/air', [LogsheetController::class, 'storeBulkAir'])->name('logsheet.bulk.air.store');
        Route::get('/logsheet/bulk/air/template', [LogsheetController::class, 'downloadBulkAirTemplate'])->name('logsheet.bulk.air.template');

        Route::get('/logsheet/manual/listrik', [LogsheetController::class, 'createManualListrik'])->name('logsheet.manual.listrik');
        Route::post('/logsheet/manual/listrik', [LogsheetController::class, 'storeManualListrik'])->name('logsheet.manual.listrik.store');
        Route::get('/logsheet/manual/air', [LogsheetController::class, 'createManualAir'])->name('logsheet.manual.air');
        Route::post('/logsheet/manual/air', [LogsheetController::class, 'storeManualAir'])->name('logsheet.manual.air.store');
        Route::get('/logsheet/manual/reference', [LogsheetController::class, 'referenceManual'])->name('logsheet.manual.reference');
    });

    Route::resource('logsheet', LogsheetController::class)->except('destroy');

    // ── Logsheet Public Area (Air & Listrik) ──
    Route::prefix('logsheet-public-area')->name('public-area.')->group(function () {
        Route::get('/', [PublicAreaLogsheetController::class, 'hub'])->name('index');

        // Sub Menu 1: Water Meter Public Area (Cooling Tower 12A, Toilet Sky Lobby 11 & Mushola 12, Pantry Office)
        Route::get('/water', [PublicAreaLogsheetController::class, 'waterIndex'])->name('water.index');
        Route::get('/water/input/{meterKey}', [PublicAreaLogsheetController::class, 'waterCreate'])->name('water.create');
        Route::post('/water/store', [PublicAreaLogsheetController::class, 'waterStore'])->name('water.store');

        // Sub Menu 2: LVSB (Low Voltage Switchboard - Pengganti KWh Meter Public Area)
        Route::get('/lvsb', [PublicAreaLvsbController::class, 'index'])->name('lvsb.index');
        Route::get('/lvsb/input', [PublicAreaLvsbController::class, 'create'])->name('lvsb.create');
        Route::post('/lvsb/store', [PublicAreaLvsbController::class, 'store'])->name('lvsb.store');
        Route::get('/lvsb/detail/{reading}', [PublicAreaLvsbController::class, 'show'])->name('lvsb.show')->whereNumber('reading');
        Route::post('/lvsb/verify/{reading}', [PublicAreaLvsbController::class, 'verify'])->name('lvsb.verify')->whereNumber('reading');
        Route::delete('/lvsb/destroy/{reading}', [PublicAreaLvsbController::class, 'destroy'])->name('lvsb.destroy')->whereNumber('reading');
        Route::get('/lvsb/export/excel', [PublicAreaLvsbController::class, 'exportExcel'])->name('lvsb.excel');
        Route::get('/lvsb/export/pdf', [PublicAreaLvsbController::class, 'exportPdf'])->name('lvsb.pdf');

        // Sub Menu 3: MVSB Incoming From PLN (Medium Voltage Switchboard 20kV)
        Route::get('/mvsb', [PublicAreaMvsbController::class, 'index'])->name('mvsb.index');
        Route::get('/mvsb/input', [PublicAreaMvsbController::class, 'create'])->name('mvsb.create');
        Route::post('/mvsb/store', [PublicAreaMvsbController::class, 'store'])->name('mvsb.store');
        Route::get('/mvsb/detail/{reading}', [PublicAreaMvsbController::class, 'show'])->name('mvsb.show')->whereNumber('reading');
        Route::post('/mvsb/verify/{reading}', [PublicAreaMvsbController::class, 'verify'])->name('mvsb.verify')->whereNumber('reading');
        Route::delete('/mvsb/destroy/{reading}', [PublicAreaMvsbController::class, 'destroy'])->name('mvsb.destroy')->whereNumber('reading');
        Route::get('/mvsb/export/excel', [PublicAreaMvsbController::class, 'exportExcel'])->name('mvsb.excel');
        Route::get('/mvsb/export/pdf', [PublicAreaMvsbController::class, 'exportPdf'])->name('mvsb.pdf');

        // Alias URL electricity lama dialihkan langsung ke LVSB
        Route::redirect('/electricity', '/logsheet-public-area/lvsb')->name('electricity.index');
        Route::redirect('/electricity/input', '/logsheet-public-area/lvsb/input')->name('electricity.create');
        Route::post('/electricity/store', [PublicAreaLogsheetController::class, 'electricityStore'])->name('electricity.store');

        // Detail, Verify & Delete
        Route::get('/detail/{reading}', [PublicAreaLogsheetController::class, 'show'])->name('show')->whereNumber('reading');
        Route::middleware('role:admin,superadmin,eng_admin,eng_spv,gm')->group(function () {
            Route::get('/export/excel/{category}', [PublicAreaLogsheetController::class, 'exportExcel'])->name('excel');
            Route::get('/export/pdf/{category}', [PublicAreaLogsheetController::class, 'exportPdf'])->name('pdf');
        });
        Route::middleware('role:admin,superadmin,eng_admin,eng_spv')->group(function () {
            Route::post('/verify/{reading}', [PublicAreaLogsheetController::class, 'verify'])->name('verify')->whereNumber('reading');
            Route::delete('/destroy/{reading}', [PublicAreaLogsheetController::class, 'destroy'])->name('destroy')->whereNumber('reading');
        });

        // ── Input Manual Back Date (Khusus Super Admin untuk Tanggal Terlewat) ──
        Route::middleware('role:admin,superadmin')->group(function () {
            // Aksi Masal Public Area (Verifikasi Masal & Hapus Masal Maksimal 1 Bulan)
            Route::post('/water/bulk-verify', [PublicAreaLogsheetController::class, 'bulkVerifyWater'])->name('water.bulk.verify');
            Route::post('/water/bulk-destroy', [PublicAreaLogsheetController::class, 'bulkDestroyWater'])->name('water.bulk.destroy');

            Route::post('/lvsb/bulk-verify', [PublicAreaLvsbController::class, 'bulkVerify'])->name('lvsb.bulk.verify');
            Route::post('/lvsb/bulk-destroy', [PublicAreaLvsbController::class, 'bulkDestroy'])->name('lvsb.bulk.destroy');

            Route::post('/mvsb/bulk-verify', [PublicAreaMvsbController::class, 'bulkVerify'])->name('mvsb.bulk.verify');
            Route::post('/mvsb/bulk-destroy', [PublicAreaMvsbController::class, 'bulkDestroy'])->name('mvsb.bulk.destroy');
            // Bulk Input Manual Water Public Area (Unggah Excel 1 Bulan)
            Route::get('/bulk/water', [PublicAreaLogsheetController::class, 'createBulkWater'])->name('bulk.water');
            Route::post('/bulk/water', [PublicAreaLogsheetController::class, 'storeBulkWater'])->name('bulk.water.store');
            Route::get('/bulk/water/template', [PublicAreaLogsheetController::class, 'downloadBulkWaterTemplate'])->name('bulk.water.template');

            // Manual Water Public Area
            Route::get('/manual/water', [PublicAreaLogsheetController::class, 'createManualWater'])->name('manual.water');
            Route::post('/manual/water', [PublicAreaLogsheetController::class, 'storeManualWater'])->name('manual.water.store');
            Route::get('/manual/water/reference', [PublicAreaLogsheetController::class, 'referenceManualWater'])->name('manual.water.reference');

            // Manual LVSB
            Route::get('/manual/lvsb', [PublicAreaLvsbController::class, 'createManual'])->name('manual.lvsb');
            Route::post('/manual/lvsb', [PublicAreaLvsbController::class, 'storeManual'])->name('manual.lvsb.store');
            Route::get('/manual/lvsb/reference', [PublicAreaLvsbController::class, 'referenceManual'])->name('manual.lvsb.reference');

            // Manual MVSB
            Route::get('/manual/mvsb', [PublicAreaMvsbController::class, 'createManual'])->name('manual.mvsb');
            Route::post('/manual/mvsb', [PublicAreaMvsbController::class, 'storeManual'])->name('manual.mvsb.store');
            Route::get('/manual/mvsb/reference', [PublicAreaMvsbController::class, 'referenceManual'])->name('manual.mvsb.reference');
        });
    });

    // ── Alias Redirects untuk /public-area/* ke /logsheet-public-area/* ──
    Route::redirect('/public-area', '/logsheet-public-area');
    Route::redirect('/public-area/water', '/logsheet-public-area/water');
    Route::redirect('/public-area/lvsb', '/logsheet-public-area/lvsb');
    Route::redirect('/public-area/mvsb', '/logsheet-public-area/mvsb');
    Route::redirect('/public-area/manual/water', '/logsheet-public-area/manual/water');
    Route::redirect('/public-area/manual/lvsb', '/logsheet-public-area/manual/lvsb');
    Route::redirect('/public-area/manual/mvsb', '/logsheet-public-area/manual/mvsb');

    // ── Daily Log Book (Pekerjaan Harian Teknisi) ──
    Route::get('/daily-log', [DailyLogBookController::class, 'index'])->name('daily-log.index');
    Route::get('/daily-log/create', [DailyLogBookController::class, 'create'])->name('daily-log.create');
    Route::post('/daily-log', [DailyLogBookController::class, 'store'])->name('daily-log.store');
    Route::get('/daily-log/{dailyLogBook}', [DailyLogBookController::class, 'show'])->name('daily-log.show');
    Route::get('/daily-log/{dailyLogBook}/edit', [DailyLogBookController::class, 'edit'])->name('daily-log.edit');
    Route::put('/daily-log/{dailyLogBook}', [DailyLogBookController::class, 'update'])->name('daily-log.update');
    Route::delete('/daily-log/{dailyLogBook}', [DailyLogBookController::class, 'destroy'])->name('daily-log.destroy');
});
