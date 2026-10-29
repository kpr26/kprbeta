<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Services\ExecutiveSummaryService;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappSettingController extends Controller
{
    /**
     * Halaman Pengaturan Notifikasi WhatsApp Otomatis (Khusus Super Admin)
     */
    public function index(ExecutiveSummaryService $summaryService)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses ditolak. Fitur ini khusus Role Super Admin.');

        $setting = WhatsappSetting::getSetting();
        $fonnteToken = $setting->getFonnteToken();
        $isFonnteConfigured = ! empty($fonnteToken);

        $todayDate = today()->toDateString();
        $messagePreview = $summaryService->formatWhatsAppMessage($todayDate);
        $summaryData = $summaryService->getData($todayDate);

        return view('admin.whatsapp.settings', compact(
            'user',
            'setting',
            'isFonnteConfigured',
            'fonnteToken',
            'messagePreview',
            'summaryData',
            'todayDate'
        ));
    }

    /**
     * Simpan Perubahan Pengaturan WhatsApp
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses ditolak. Fitur ini khusus Role Super Admin.');

        $request->validate([
            'fonnte_token'             => 'nullable|string|max:150',
            'scheduled_time'           => ['required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'header_template'          => 'nullable|string|max:2000',
            'reminder_template'        => 'nullable|string|max:1000',
            'success_template'         => 'nullable|string|max:1000',
            'footer_template'          => 'nullable|string|max:1000',
            // Multiple WhatsApp Groups
            'groups'                   => 'nullable|array',
            'groups.*.id'              => 'nullable|string|max:120',
            'groups.*.name'            => 'nullable|string|max:120',
            'groups.*.is_active'       => 'nullable',
            'target_group_id'          => 'nullable|string|max:120',
            'target_group_name'        => 'nullable|string|max:120',
            // Multiple Phone Numbers
            'phone_numbers'            => 'nullable|array',
            'phone_numbers.*.number'   => 'nullable|string|max:30',
            'phone_numbers.*.name'     => 'nullable|string|max:80',
            'phone_numbers.*.is_active'=> 'nullable',
        ]);

        $setting = WhatsappSetting::getSetting();

        // 1. Format dan bersihkan daftar Grup WhatsApp terpilih
        $cleanGroups = [];
        if ($request->has('groups') && is_array($request->groups)) {
            foreach ($request->groups as $item) {
                $id = trim($item['id'] ?? '');
                if (! empty($id)) {
                    $isActive = filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($isActive === null) {
                        $isActive = ! empty($item['is_active']);
                    }
                    $cleanGroups[] = [
                        'id'        => $id,
                        'name'      => trim($item['name'] ?? ''),
                        'is_active' => (bool) $isActive,
                    ];
                }
            }
        }

        // Fallback backward compatibility jika input target_group_id dikirim langsung
        $fallbackGroupId = trim($request->input('target_group_id', ''));
        if (empty($cleanGroups) && ! empty($fallbackGroupId)) {
            $cleanGroups[] = [
                'id'        => $fallbackGroupId,
                'name'      => trim($request->input('target_group_name', 'Grup Utama WhatsApp KPR')),
                'is_active' => true,
            ];
        }

        // 2. Format dan bersihkan daftar nomor WhatsApp terpilih
        $cleanNumbers = [];
        if ($request->has('phone_numbers') && is_array($request->phone_numbers)) {
            foreach ($request->phone_numbers as $item) {
                $rawNumber = trim($item['number'] ?? '');
                $num = preg_replace('/[^0-9+]/', '', $rawNumber);

                // Standarisasi format 08xx ke 628xx jika diperlukan
                if (str_starts_with($num, '08')) {
                    $num = '628' . substr($num, 2);
                } elseif (str_starts_with($num, '+')) {
                    $num = substr($num, 1);
                }

                if (! empty($num)) {
                    $isActive = filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($isActive === null) {
                        $isActive = ! empty($item['is_active']);
                    }
                    $cleanNumbers[] = [
                        'number'    => $num,
                        'name'      => trim($item['name'] ?? ''),
                        'is_active' => (bool) $isActive,
                    ];
                }
            }
        }

        $primaryGroup = $cleanGroups[0] ?? null;
        $submittedToken = trim($request->input('fonnte_token', ''));

        $setting->update([
            'fonnte_token'         => ! empty($submittedToken) ? $submittedToken : $setting->fonnte_token,
            'target_group_id'      => $primaryGroup ? $primaryGroup['id'] : null,
            'target_group_name'    => $primaryGroup ? $primaryGroup['name'] : null,
            'target_groups'        => $cleanGroups,
            'target_phone_numbers' => $cleanNumbers,
            'scheduled_time'       => $request->input('scheduled_time', '18:00'),
            'is_active'            => $request->boolean('is_active', false),
            'send_to_group'        => $request->boolean('send_to_group', false),
            'send_to_numbers'      => $request->boolean('send_to_numbers', false),
            'header_template'      => $request->input('header_template'),
            'reminder_template'    => $request->input('reminder_template'),
            'success_template'     => $request->input('success_template'),
            'footer_template'      => $request->input('footer_template'),
            'updated_by'           => $user->id,
        ]);

        return redirect()->route('admin.whatsapp.settings')
            ->with('success', 'Pengaturan notifikasi WhatsApp otomatis berhasil diperbarui!');
    }

    /**
     * AJAX Live Preview Format Pesan
     */
    public function preview(Request $request, ExecutiveSummaryService $summaryService)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses ditolak.');

        $date = $request->input('date', today()->toDateString());
        $message = $summaryService->formatWhatsAppMessage($date);
        $data = $summaryService->getData($date);

        return response()->json([
            'status'  => true,
            'date'    => $date,
            'message' => $message,
            'summary' => [
                'formatted_date'   => $data['formatted_date'],
                'all_completed'    => $data['all_completed'],
                'unrecorded_count' => $data['unrecorded_count'],
                'unrecorded_items' => $data['unrecorded_items'],
            ],
        ]);
    }

    /**
     * Kirim Test Notifikasi WhatsApp (Manual / On Demand)
     */
    public function sendTest(Request $request, ExecutiveSummaryService $summaryService, FonnteService $fonnteService)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses ditolak.');

        $setting = WhatsappSetting::getSetting();
        $activeToken = $setting->getFonnteToken();

        if (empty($activeToken)) {
            return back()->with('error', 'Gagal: Token API Fonnte belum diisi. Masukkan token di formulir pengaturan terlebih dahulu.');
        }

        $date = $request->input('date', today()->toDateString());
        $customTarget = trim($request->input('custom_target', ''));
        $sendMode = $request->input('send_mode', 'all'); // 'all', 'group_only', 'numbers_only', 'custom'

        $message = $summaryService->formatWhatsAppMessage($date);

        $targets = [];
        if ($sendMode === 'custom' && ! empty($customTarget)) {
            $targets[] = $customTarget;
        } elseif ($sendMode === 'group_only') {
            foreach ($setting->getActiveGroups() as $group) {
                if (! in_array($group['id'], $targets, true)) {
                    $targets[] = $group['id'];
                }
            }
        } elseif ($sendMode === 'numbers_only') {
            foreach ($setting->getActivePhoneNumbers() as $item) {
                $num = preg_replace('/[^0-9@.a-zA-Z_-]/', '', $item['number']);
                if (! empty($num) && ! in_array($num, $targets, true)) {
                    $targets[] = $num;
                }
            }
        } else {
            // Mode All (Grup & Nomor Terpilih)
            $targets = $setting->getAllActiveTargets();
            if (empty($targets) && ! empty($customTarget)) {
                $targets[] = $customTarget;
            }
        }

        if (empty($targets)) {
            return back()->with('error', 'Tidak ada target tujuan aktif yang dipilih atau ditentukan.');
        }

        $successCount = 0;
        $failCount = 0;
        $lastError = '';

        foreach ($targets as $target) {
            $result = $fonnteService->sendMessage($target, $message);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $lastError = $result['message'] ?? 'Error pengiriman.';
            }
        }

        if ($successCount > 0 && $failCount === 0) {
            return back()->with('success', "Notifikasi WhatsApp berhasil dikirim ke {$successCount} target penerima!");
        } elseif ($successCount > 0 && $failCount > 0) {
            return back()->with('warning', "Terkirim ke {$successCount} target, namun {$failCount} target gagal: {$lastError}");
        } else {
            return back()->with('error', "Gagal mengirim WhatsApp ke target: {$lastError}");
        }
    }
}
