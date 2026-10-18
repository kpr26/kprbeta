<?php

namespace App\Http\Controllers;

use App\Models\DailyLogBook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DailyLogBookController extends Controller
{
    /**
     * Tampilkan daftar rekap pekerjaan harian (Bisa dilihat oleh SEMUA role).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isTeknisi = $user->role === 'teknisi';

        // Filter parameter
        $filters = [
            'search'  => $request->query('search'),
            'status'  => $request->query('status'),
            'user_id' => $request->query('user_id'),
            'tanggal' => $request->query('tanggal'),
            'bulan'   => $request->query('bulan', now()->format('Y-m')),
        ];

        // Jika filter tanggal diisi khusus, kosongkan filter bulan
        if ($request->filled('tanggal')) {
            $filters['bulan'] = null;
        }

        $query = DailyLogBook::with('user')->orderByDesc('tanggal')->orderByDesc('id');

        // Filter logic
        $query->filter($filters);

        $logs = $query->paginate(15)->withQueryString();

        // Ringkasan metrik statistik
        $currentMonth = $filters['bulan'] ?: now()->format('Y-m');
        $monthQuery = DailyLogBook::query();
        if ($currentMonth) {
            $parts = explode('-', $currentMonth);
            if (count($parts) === 2) {
                $monthQuery->whereYear('tanggal', $parts[0])->whereMonth('tanggal', $parts[1]);
            }
        }

        $summary = [
            'total_pekerjaan' => (clone $monthQuery)->count(),
            'done'            => (clone $monthQuery)->where('status', 'Done')->count(),
            'continue'        => (clone $monthQuery)->where('status', 'Continue')->count(),
            'today_count'     => DailyLogBook::whereDate('tanggal', today())->count(),
        ];

        // Daftar teknisi untuk filter dropdown
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('daily-log.index', compact('logs', 'summary', 'filters', 'technicians', 'isTeknisi'));
    }

    /**
     * Form input pekerjaan harian baru (Khusus Teknisi & Admin).
     */
    public function create()
    {
        $today = today();
        $defaultStartTime = now()->subHours(2)->format('H:i');
        $defaultEndTime = now()->format('H:i');

        return view('daily-log.create', [
            'defaultDate'      => $today->toDateString(),
            'defaultStartTime' => $defaultStartTime,
            'defaultEndTime'   => $defaultEndTime,
        ]);
    }

    /**
     * Simpan input pekerjaan harian dengan watermark pada foto.
     */
    public function store(Request $request)
    {
        // Hapus field base64 dari input request agar tidak masuk ke session payload di database
        $request->request->remove('foto_awal_base64');
        $request->request->remove('foto_proses_base64');
        $request->request->remove('foto_akhir_base64');

        $request->validate([
            'tanggal'      => ['required', 'date'],
            'jam_mulai'    => ['required', 'string', 'max:10'],
            'jam_selesai'  => ['required', 'string', 'max:10'],
            'lokasi'       => ['required', 'string', 'max:255'],
            'status'       => ['required', 'in:Done,Continue'],
            'deskripsi'    => ['required', 'string'],
            'catatan'      => ['nullable', 'string', 'max:2000'],
            'foto_awal'    => ['required'],
            'foto_proses'  => ['required'],
            'foto_akhir'   => ['required'],
        ], [
            'tanggal.required'     => 'Hari dan tanggal pekerjaan wajib diisi.',
            'jam_mulai.required'   => 'Jam start (mulai) pekerjaan wajib diisi.',
            'jam_selesai.required' => 'Jam selesai pekerjaan wajib diisi.',
            'lokasi.required'      => 'Lokasi pekerjaan wajib diisi.',
            'status.required'      => 'Status pekerjaan (Done / Continue) wajib dipilih.',
            'deskripsi.required'   => 'Deskripsi rincian pekerjaan wajib diisi.',
            'foto_awal.required'   => 'Foto awal pekerjaan wajib dilampirkan.',
            'foto_proses.required' => 'Foto proses pekerjaan wajib dilampirkan.',
            'foto_akhir.required'  => 'Foto akhir pekerjaan wajib dilampirkan.',
        ]);

        $tanggalObj = Carbon::parse($request->input('tanggal'));
        $tanggalFormatted = $tanggalObj->translatedFormat('l, d F Y');
        $jamText = $request->input('jam_mulai') . ' - ' . $request->input('jam_selesai') . ' WIB';
        $lokasi = $request->input('lokasi');
        $teknisiName = Auth::user()->name;

        // Pastikan direktori storage/daily_logs ada
        Storage::disk('public')->makeDirectory('daily_logs');

        // Simpan ketiga foto dengan watermark
        $fotoAwalPath = $this->handlePhotoUpload(
            $request,
            'foto_awal',
            'awal',
            $tanggalFormatted,
            $jamText,
            $lokasi,
            'Foto Awal Pekerjaan',
            $teknisiName
        );

        $fotoProsesPath = $this->handlePhotoUpload(
            $request,
            'foto_proses',
            'proses',
            $tanggalFormatted,
            $jamText,
            $lokasi,
            'Foto Proses Pekerjaan',
            $teknisiName
        );

        $fotoAkhirPath = $this->handlePhotoUpload(
            $request,
            'foto_akhir',
            'akhir',
            $tanggalFormatted,
            $jamText,
            $lokasi,
            'Foto Akhir Pekerjaan',
            $teknisiName
        );

        $log = DailyLogBook::create([
            'user_id'     => Auth::id(),
            'tanggal'     => $request->input('tanggal'),
            'jam_mulai'   => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
            'lokasi'      => $lokasi,
            'status'      => $request->input('status'),
            'deskripsi'   => $request->input('deskripsi'),
            'catatan'     => $request->input('catatan'),
            'foto_awal'   => $fotoAwalPath,
            'foto_proses' => $fotoProsesPath,
            'foto_akhir'  => $fotoAkhirPath,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('daily-log.show', $log),
                'message'  => 'Daily Log Book berhasil dicatat! Foto pekerjaan telah dilengkapi watermark tanggal, jam, dan lokasi.',
            ]);
        }

        return redirect()->route('daily-log.show', $log)
            ->with('success', 'Daily Log Book berhasil dicatat! Foto pekerjaan telah dilengkapi watermark tanggal, jam, dan lokasi.');
    }

    /**
     * Tampilkan detail pekerjaan harian (Dapat dilihat oleh SEMUA role).
     */
    public function show(DailyLogBook $dailyLogBook)
    {
        $dailyLogBook->load('user');

        $canEdit = Auth::id() === $dailyLogBook->user_id || in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);

        return view('daily-log.show', [
            'log'     => $dailyLogBook,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Form edit logbook (Pemilik atau Admin).
     */
    public function edit(DailyLogBook $dailyLogBook)
    {
        $canEdit = Auth::id() === $dailyLogBook->user_id || in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);

        if (! $canEdit) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit data logbook ini.');
        }

        return view('daily-log.edit', [
            'log' => $dailyLogBook,
        ]);
    }

    /**
     * Update logbook.
     */
    public function update(Request $request, DailyLogBook $dailyLogBook)
    {
        $canEdit = Auth::id() === $dailyLogBook->user_id || in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);

        if (! $canEdit) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit data logbook ini.');
        }

        // Hapus field base64 dari input request agar tidak masuk ke session payload di database
        $request->request->remove('foto_awal_base64');
        $request->request->remove('foto_proses_base64');
        $request->request->remove('foto_akhir_base64');

        $request->validate([
            'tanggal'      => ['required', 'date'],
            'jam_mulai'    => ['required', 'string', 'max:10'],
            'jam_selesai'  => ['required', 'string', 'max:10'],
            'lokasi'       => ['required', 'string', 'max:255'],
            'status'       => ['required', 'in:Done,Continue'],
            'deskripsi'    => ['required', 'string'],
            'catatan'      => ['nullable', 'string', 'max:2000'],
            'foto_awal'    => ['nullable'],
            'foto_proses'  => ['nullable'],
            'foto_akhir'   => ['nullable'],
        ]);

        $tanggalObj = Carbon::parse($request->input('tanggal'));
        $tanggalFormatted = $tanggalObj->translatedFormat('l, d F Y');
        $jamText = $request->input('jam_mulai') . ' - ' . $request->input('jam_selesai') . ' WIB';
        $lokasi = $request->input('lokasi');
        $teknisiName = $dailyLogBook->user->name;

        $updateData = [
            'tanggal'     => $request->input('tanggal'),
            'jam_mulai'   => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
            'lokasi'      => $lokasi,
            'status'      => $request->input('status'),
            'deskripsi'   => $request->input('deskripsi'),
            'catatan'     => $request->input('catatan'),
        ];

        // Jika foto awal diganti
        if ($request->filled('foto_awal') || $request->hasFile('foto_awal')) {
            if ($dailyLogBook->foto_awal) {
                Storage::disk('public')->delete($dailyLogBook->foto_awal);
            }
            $updateData['foto_awal'] = $this->handlePhotoUpload(
                $request, 'foto_awal', 'awal', $tanggalFormatted, $jamText, $lokasi, 'Foto Awal Pekerjaan', $teknisiName
            );
        }

        // Jika foto proses diganti
        if ($request->filled('foto_proses') || $request->hasFile('foto_proses')) {
            if ($dailyLogBook->foto_proses) {
                Storage::disk('public')->delete($dailyLogBook->foto_proses);
            }
            $updateData['foto_proses'] = $this->handlePhotoUpload(
                $request, 'foto_proses', 'proses', $tanggalFormatted, $jamText, $lokasi, 'Foto Proses Pekerjaan', $teknisiName
            );
        }

        // Jika foto akhir diganti
        if ($request->filled('foto_akhir') || $request->hasFile('foto_akhir')) {
            if ($dailyLogBook->foto_akhir) {
                Storage::disk('public')->delete($dailyLogBook->foto_akhir);
            }
            $updateData['foto_akhir'] = $this->handlePhotoUpload(
                $request, 'foto_akhir', 'akhir', $tanggalFormatted, $jamText, $lokasi, 'Foto Akhir Pekerjaan', $teknisiName
            );
        }

        $dailyLogBook->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('daily-log.show', $dailyLogBook),
                'message'  => 'Data Daily Log Book berhasil diperbarui!',
            ]);
        }

        return redirect()->route('daily-log.show', $dailyLogBook)
            ->with('success', 'Data Daily Log Book berhasil diperbarui!');
    }

    /**
     * Hapus logbook (Admin / SPV atau pembuat log).
     */
    public function destroy(DailyLogBook $dailyLogBook)
    {
        $canDelete = Auth::id() === $dailyLogBook->user_id || in_array(Auth::user()->role, ['admin', 'eng_admin', 'eng_spv'], true);

        if (! $canDelete) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus data logbook ini.');
        }

        if ($dailyLogBook->foto_awal) {
            Storage::disk('public')->delete($dailyLogBook->foto_awal);
        }
        if ($dailyLogBook->foto_proses) {
            Storage::disk('public')->delete($dailyLogBook->foto_proses);
        }
        if ($dailyLogBook->foto_akhir) {
            Storage::disk('public')->delete($dailyLogBook->foto_akhir);
        }

        $dailyLogBook->delete();

        return redirect()->route('daily-log.index')
            ->with('success', 'Data Daily Log Book telah berhasil dihapus.');
    }

    /**
     * Helper untuk memproses upload foto (File multipart atau Data URL Base64 dari Canvas)
     * dan menerapkan server-side watermark via GD jika diperlukan.
     */
    protected function handlePhotoUpload(
        Request $request,
        string $field,
        string $prefix,
        string $tanggal,
        string $jam,
        string $lokasi,
        string $tahap,
        string $teknisi
    ): string {
        $filename = 'daily_logs/' . $prefix . '_' . date('Ymd_His') . '_' . Str::random(8) . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);

        if (! is_dir(dirname($fullPath))) {
            @mkdir(dirname($fullPath), 0755, true);
        }

        // Cek apakah ada base64 data url dari canvas (client-side watermarked)
        $base64Input = $request->input($field . '_base64');
        $isClientWatermarked = $request->boolean($field . '_is_watermarked');

        if ($base64Input && str_starts_with($base64Input, 'data:image')) {
            $dataParts = explode(',', $base64Input);
            $imageData = base64_decode(end($dataParts));
            file_put_contents($fullPath, $imageData);
        } elseif ($request->hasFile($field)) {
            $file = $request->file($field);
            $file->move(dirname($fullPath), basename($fullPath));
        } elseif (is_string($request->input($field)) && str_starts_with($request->input($field), 'data:image')) {
            $dataParts = explode(',', $request->input($field));
            $imageData = base64_decode(end($dataParts));
            file_put_contents($fullPath, $imageData);
        } else {
            throw new \InvalidArgumentException("Format foto {$field} tidak dikenali.");
        }

        // Jika belum diberi watermark di client side, atau untuk memastikan watermark permanen:
        if (! $isClientWatermarked && file_exists($fullPath)) {
            $this->stampWatermarkOnImage($fullPath, $tanggal, $jam, $lokasi, $tahap, $teknisi);
        }

        return $filename;
    }

    /**
     * Server-side PHP GD Watermarking Engine.
     * Menempelkan banner watermark rapi berisi Tanggal, Jam, Lokasi, Tahap, & Teknisi pada foto.
     */
    protected function stampWatermarkOnImage(
        string $fullPath,
        string $tanggal,
        string $jam,
        string $lokasi,
        string $tahap,
        string $teknisi
    ): void {
        if (! extension_loaded('gd') || ! file_exists($fullPath)) {
            return;
        }

        $imageInfo = @getimagesize($fullPath);
        if (! $imageInfo) {
            return;
        }

        $mime = $imageInfo['mime'];
        $srcImage = match ($mime) {
            'image/jpeg', 'image/pjpeg' => @imagecreatefromjpeg($fullPath),
            'image/png'                 => @imagecreatefrompng($fullPath),
            'image/webp'                => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : null,
            default                     => null,
        };

        if (! $srcImage) {
            return;
        }

        $origW = imagesx($srcImage);
        $origH = imagesy($srcImage);

        // Skalakan gambar jika terlalu besar untuk efisiensi penyimpanan & rendering cepat (max 1600px)
        $maxDimension = 1600;
        if ($origW > $maxDimension || $origH > $maxDimension) {
            $ratio = min($maxDimension / $origW, $maxDimension / $origH);
            $newW = (int) ($origW * $ratio);
            $newH = (int) ($origH * $ratio);
            $resizedImage = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resizedImage, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($srcImage);
            $image = $resizedImage;
            $w = $newW;
            $h = $newH;
        } else {
            $image = $srcImage;
            $w = $origW;
            $h = $origH;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        // ==========================================
        // WATERMARK KARTU POJOK KANAN BAWAH (STYLE APLIKASI MARKI)
        // ==========================================
        $font = 4; // Built-in GD font (lebar ~8px, tinggi ~16px)
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);

        $line1 = "[KPR BETA] " . strtoupper($tahap);
        $line2 = "TANGGAL: " . $tanggal;
        $line3 = "WAKTU  : " . $jam;
        $line4 = "LOKASI : " . mb_strimwidth($lokasi, 0, 42, '...');
        $line5 = "PETUGAS: " . mb_strimwidth($teknisi, 0, 36, '...');

        $maxLen = max(strlen($line1), strlen($line2), strlen($line3), strlen($line4), strlen($line5));
        $padX = 18;
        $padY = 14;
        $barW = 5;
        $innerLeft = $padX + $barW + 8;

        $boxW = ($maxLen * $charW) + $innerLeft + $padX;
        $lineSpacing = $charH + 5;
        $boxH = ($padY * 2) + ($lineSpacing * 4) + $charH;

        $margin = max(16, (int) ($w * 0.02));
        $boxX = max(0, $w - $boxW - $margin);
        $boxY = max(0, $h - $boxH - $margin);

        // 1. Dark background overlay (semi-transparan ~82%)
        $darkBg = imagecolorallocatealpha($image, 10, 15, 29, 22);
        imagefilledrectangle($image, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $darkBg);

        // 2. Border kartu tipis
        $borderColor = imagecolorallocatealpha($image, 255, 255, 255, 95);
        imagerectangle($image, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $borderColor);

        // 3. Garis aksen vertikal di kiri kartu (Amber Gold)
        $accentGold = imagecolorallocate($image, 245, 158, 11);
        imagefilledrectangle($image, $boxX + $padX - 4, $boxY + 6, $boxX + $padX - 4 + $barW, $boxY + $boxH - 6, $accentGold);

        // 4. Warna teks
        $textWhite  = imagecolorallocate($image, 255, 255, 255);
        $textYellow = imagecolorallocate($image, 251, 191, 36);
        $textCyan   = imagecolorallocate($image, 56, 189, 248);
        $textMuted  = imagecolorallocate($image, 148, 163, 184);

        // 5. Gambar Teks
        $textX = $boxX + $innerLeft;
        $textY = $boxY + $padY;

        imagestring($image, $font, $textX, $textY, $line1, $textYellow);
        $textY += $lineSpacing;

        imagestring($image, $font, $textX, $textY, $line2, $textWhite);
        $textY += $lineSpacing;

        imagestring($image, $font, $textX, $textY, $line3, $textWhite);
        $textY += $lineSpacing;

        imagestring($image, $font, $textX, $textY, $line4, $textCyan);
        $textY += $lineSpacing;

        imagestring($image, $font, $textX, $textY, $line5, $textMuted);

        // Simpan hasil ke file JPEG berkualitas tinggi
        imagejpeg($image, $fullPath, 88);
        imagedestroy($image);
    }
}
