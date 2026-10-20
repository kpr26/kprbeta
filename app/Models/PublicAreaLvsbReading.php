<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicAreaLvsbReading extends Model
{
    use HasFactory;

    protected $table = 'public_area_lvsb_readings';

    protected $fillable = [
        'tanggal',
        'readings',
        'fotos',
        'foto',
        'catatan',
        'user_id',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'readings'    => 'array',
        'fotos'       => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Definisi Struktur Form & Data LVSB (Low Voltage Switchboard)
     * Terdiri dari 6 grup sesuai spesifikasi panel:
     * 1. Incoming From Trafo Apart A/B (6 fields)
     * 2. Outgoing - 1.2 (Riser 3, OV AC Sky Lobby, Ac R. Panel & R. Trafo - masing-masing 6 fields)
     * 3. Outgoing - 1.1 (Riser 4 - 6 fields)
     * 4. Outgoing - 2.1 (Riser 2, DB Fire 2 Level 12, Spare - masing-masing 4 fields)
     * 5. Outgoing - 2.2 (Riser 1, Riser 5 - masing-masing 6 fields)
     * 6. Incoming Capacitor (Power Factor PF)
     */
    public static function structure(): array
    {
        return [
            'incoming_trafo' => [
                'title'         => 'Incoming From Trafo Apart A/B',
                'badge'         => 'Main Incoming',
                'color'         => '#fbbf24',
                'has_subgroups' => false,
                'fields'        => [
                    'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                    'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                    'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                    'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                    'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                    'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                ],
            ],

            'outgoing_1_2' => [
                'title'         => 'Outgoing - 1.2',
                'badge'         => 'Feeder 1.2',
                'color'         => '#38bdf8',
                'has_subgroups' => true,
                'subgroups'     => [
                    'riser_3' => [
                        'label'  => 'Riser 3',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                    'ov_ac_sky_lobby' => [
                        'label'  => 'OV AC Sky Lobby',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                    'ac_r_panel_trafo' => [
                        'label'  => 'Ac R. Panel & R. Trafo',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                ],
            ],

            'outgoing_1_1' => [
                'title'         => 'Outgoing - 1.1',
                'badge'         => 'Feeder 1.1',
                'color'         => '#818cf8',
                'has_subgroups' => true,
                'subgroups'     => [
                    'riser_4' => [
                        'label'  => 'Riser 4',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                ],
            ],

            'outgoing_2_1' => [
                'title'         => 'Outgoing - 2.1',
                'badge'         => 'Feeder 2.1',
                'color'         => '#f43f5e',
                'has_subgroups' => true,
                'subgroups'     => [
                    'riser_2' => [
                        'label'  => 'Riser 2',
                        'fields' => [
                            'voltage_3p' => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'    => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'      => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'     => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                        ],
                    ],
                    'db_fire_2_lvl_12' => [
                        'label'  => 'DB Fire 2 Level 12',
                        'fields' => [
                            'voltage_3p' => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'    => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'      => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'     => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                        ],
                    ],
                    'spare' => [
                        'label'  => 'Spare',
                        'fields' => [
                            'voltage_3p' => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'    => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'      => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'     => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                        ],
                    ],
                ],
            ],

            'outgoing_2_2' => [
                'title'         => 'Outgoing - 2.2',
                'badge'         => 'Feeder 2.2',
                'color'         => '#10b981',
                'has_subgroups' => true,
                'subgroups'     => [
                    'riser_1' => [
                        'label'  => 'Riser 1',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                    'riser_5' => [
                        'label'  => 'Riser 5',
                        'fields' => [
                            'voltage_3p'   => ['label' => 'Voltage 3-P', 'unit' => 'Volt', 'step' => '0.1', 'placeholder' => '380.0'],
                            'current'      => ['label' => 'Current', 'unit' => 'Ampere', 'step' => '0.01', 'placeholder' => '0.00'],
                            'power'        => ['label' => 'Power', 'unit' => 'kW Total', 'step' => '0.01', 'placeholder' => '0.00'],
                            'energy'       => ['label' => 'Energy', 'unit' => 'GWh', 'step' => '0.0001', 'placeholder' => '0.0000'],
                            'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.95'],
                            'frequency'    => ['label' => 'Frequency', 'unit' => 'Hz', 'step' => '0.1', 'placeholder' => '50.0'],
                        ],
                    ],
                ],
            ],

            'incoming_capacitor' => [
                'title'         => 'Incoming Capacitor',
                'badge'         => 'Capacitor Bank',
                'color'         => '#eab308',
                'has_subgroups' => false,
                'fields'        => [
                    'power_factor' => ['label' => 'Power Factor (PF)', 'unit' => 'PF', 'step' => '0.01', 'placeholder' => '0.98'],
                ],
            ],
        ];
    }

    /**
     * Helper untuk mengambil nilai spesifik dari array readings
     */
    public function val(string $group, ?string $sub = null, ?string $field = null)
    {
        $raw = $this->readings ?? [];

        if ($sub === null && $field === null) {
            return $raw[$group] ?? null;
        }

        if ($field === null) {
            // Jika grup tidak punya sub-group, parameter ke-2 adalah field
            return $raw[$group][$sub] ?? null;
        }

        return $raw[$group][$sub][$field] ?? null;
    }

    /**
     * Ambil foto untuk grup tertentu (fallback ke foto lama jika belum per-grup)
     */
    public function getFoto(string $groupKey): ?string
    {
        return $this->fotos[$groupKey] ?? null;
    }

    /**
     * Ambil seluruh path foto yang terlampir
     */
    public function getAllFotos(): array
    {
        $list = [];
        if (is_array($this->fotos)) {
            foreach ($this->fotos as $k => $f) {
                if ($f) $list[$k] = $f;
            }
        }
        if ($this->foto && !in_array($this->foto, $list, true)) {
            $list['general'] = $this->foto;
        }
        return $list;
    }

    /**
     * Relasi ke Teknisi pencatat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Verifikator (Supervisor / Super Admin)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope filter per bulan dan tahun
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('tanggal', $year)
                     ->whereMonth('tanggal', $month);
    }
}
