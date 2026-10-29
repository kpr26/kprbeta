<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappSetting extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notification_settings';

    protected $fillable = [
        'target_group_id',
        'target_group_name',
        'target_groups',
        'target_phone_numbers',
        'scheduled_time',
        'is_active',
        'send_to_group',
        'send_to_numbers',
        'header_template',
        'reminder_template',
        'success_template',
        'footer_template',
        'updated_by',
    ];

    protected $casts = [
        'target_groups'        => 'array',
        'target_phone_numbers' => 'array',
        'is_active'            => 'boolean',
        'send_to_group'        => 'boolean',
        'send_to_numbers'      => 'boolean',
    ];

    /**
     * Ambil setting saat ini (Singleton Pattern) atau buat default jika belum ada
     */
    public static function getSetting(): self
    {
        $setting = static::first();

        if (! $setting) {
            $defaultGroupId = config('services.fonnte.target_group');
            $setting = static::create([
                'target_group_id'      => $defaultGroupId,
                'target_group_name'    => 'Grup Utama WhatsApp KPR',
                'target_groups'        => $defaultGroupId ? [
                    ['id' => $defaultGroupId, 'name' => 'Grup Utama WhatsApp KPR', 'is_active' => true]
                ] : [],
                'target_phone_numbers' => [],
                'scheduled_time'       => '18:00',
                'is_active'            => true,
                'send_to_group'        => true,
                'send_to_numbers'      => true,
                'header_template'      => "🏢 *KPR TOWER - EXECUTIVE SUMMARY & LOGSHEET PULSE*\n📅 *{tanggal}* | ⏰ *{waktu}*",
                'reminder_template'    => "👉 _Mohon teknisi / tim engineering on-duty segera melengkapi logsheet di aplikasi KPRBeta sebelum akhir shift._",
                'success_template'     => "Seluruh parameter logsheet hari ini telah berhasil dicatat oleh tim on-duty.",
                'footer_template'      => "_Notifikasi harian otomatis via Fonnte Gateway_",
            ]);
        } elseif (empty($setting->target_groups) && ! empty($setting->target_group_id)) {
            // Migrasi otomatis data grup lama ke format multi-grup
            $setting->target_groups = [
                [
                    'id'        => $setting->target_group_id,
                    'name'      => $setting->target_group_name ?: 'Grup WhatsApp KPR',
                    'is_active' => true,
                ]
            ];
            $setting->saveQuietly();
        }

        return $setting;
    }

    /**
     * Mengambil daftar grup WhatsApp yang terpilih / aktif saja
     *
     * @return array
     */
    public function getActiveGroups(): array
    {
        $active = [];
        $groups = $this->target_groups;

        if (is_array($groups) && count($groups) > 0) {
            foreach ($groups as $group) {
                if (! is_array($group)) continue;
                $id = trim($group['id'] ?? '');
                $isActive = filter_var($group['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)($group['is_active'] ?? true);
                if (! empty($id) && $isActive) {
                    $active[] = [
                        'id'        => $id,
                        'name'      => trim($group['name'] ?? ''),
                        'is_active' => true,
                    ];
                }
            }
        } elseif (! empty($this->target_group_id)) {
            $active[] = [
                'id'        => trim($this->target_group_id),
                'name'      => trim($this->target_group_name ?? 'Grup Utama WhatsApp KPR'),
                'is_active' => true,
            ];
        }

        return $active;
    }

    /**
     * Mengambil daftar nomor WhatsApp yang terpilih / aktif saja
     *
     * @return array
     */
    public function getActivePhoneNumbers(): array
    {
        $active = [];
        $numbers = $this->target_phone_numbers;

        if (is_array($numbers)) {
            foreach ($numbers as $item) {
                if (! is_array($item)) {
                    $num = trim((string)$item);
                    if (! empty($num)) {
                        $active[] = ['number' => $num, 'name' => '', 'is_active' => true];
                    }
                    continue;
                }

                $num = trim($item['number'] ?? '');
                $isActive = filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)($item['is_active'] ?? true);
                if (! empty($num) && $isActive) {
                    $active[] = [
                        'number'    => $num,
                        'name'      => trim($item['name'] ?? ''),
                        'is_active' => true,
                    ];
                }
            }
        }

        return $active;
    }

    /**
     * Mengumpulkan seluruh target aktif (Grup dan Nomor HP Terpilih)
     *
     * @return array
     */
    public function getAllActiveTargets(): array
    {
        $targets = [];

        // 1. WhatsApp Groups
        if ($this->send_to_group) {
            foreach ($this->getActiveGroups() as $group) {
                $id = $group['id'];
                if (! in_array($id, $targets, true)) {
                    $targets[] = $id;
                }
            }
        }

        // 2. WhatsApp Phone Numbers
        if ($this->send_to_numbers) {
            foreach ($this->getActivePhoneNumbers() as $item) {
                $num = preg_replace('/[^0-9@.a-zA-Z_-]/', '', $item['number']);
                if (! empty($num) && ! in_array($num, $targets, true)) {
                    $targets[] = $num;
                }
            }
        }

        return $targets;
    }

    /**
     * Relasi ke user yang terakhir memperbarui setting
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
