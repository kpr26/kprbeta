<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantEnergyReading;
use App\Models\TenantWaterReading;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class October2026BillingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'superadmin'])->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        // ==========================================
        // 1. Baca Data Listrik & Master Tenant
        // ==========================================
        $energyCsvPath = database_path('seeders/data/october_2026_raw.csv');
        if (! file_exists($energyCsvPath)) {
            $this->command->error("File CSV Listrik tidak ditemukan: {$energyCsvPath}");
            return;
        }

        $lines = file($energyCsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = str_getcsv(array_shift($lines));

        $unitsData = [];
        $evRows = [];

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) < 7) continue;

            $no        = (int) trim($row[0]);
            $floorRaw  = trim($row[1]);
            $unitCode  = trim($row[2]);
            $daya      = (float) $row[3];
            $awal      = (float) $row[4];
            $akhir     = (float) $row[5];
            $pemakaian = (float) $row[6];
            $ket       = isset($row[14]) ? trim($row[14]) : null;

            // Opsi B: Proteksi anomali stand mundur / KWH error agar pemakaian tidak minus
            if ($pemakaian < 0 || ($akhir < $awal)) {
                $pemakaian = 0.00;
                $ket = $ket ?: 'KWH ERROR (Stand Mundur/Prorate Manual)';
            }

            $isEv = (stripos($floorRaw, 'CAR') !== false);
            $cleanFloor = trim(str_ireplace(['/ CAR', '/CAR', 'CAR', '/'], '', $floorRaw));
            $unitKey = $cleanFloor . '_' . $unitCode;

            if ($isEv) {
                $evRows[$unitKey] = [
                    'daya'       => $daya,
                    'awal'       => $awal,
                    'akhir'      => $akhir,
                    'pemakaian'  => $pemakaian,
                    'keterangan' => $ket,
                ];
            } else {
                $unitsData[$unitKey] = [
                    'floor'      => $cleanFloor,
                    'unit'       => $unitCode,
                    'daya'       => $daya,
                    'awal'       => $awal,
                    'akhir'      => $akhir,
                    'pemakaian'  => $pemakaian,
                    'keterangan' => $ket,
                ];
            }
        }

        // ==========================================
        // 2. Baca Data Air (October 2026)
        // ==========================================
        $waterCsvPath = database_path('seeders/data/october_2026_water_raw.csv');
        $waterData = [];
        if (file_exists($waterCsvPath)) {
            $wLines = file($waterCsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            array_shift($wLines); // header: No,Floor,Unit,Awal,Akhir,Pemakaian,Keterangan
            foreach ($wLines as $wLine) {
                $wRow = str_getcsv($wLine);
                if (count($wRow) < 6) continue;

                $wFloorRaw = trim($wRow[1]);
                $wUnit     = trim($wRow[2]);
                $isEv      = (stripos($wFloorRaw, 'CAR') !== false);
                if ($isEv) continue; // Skip EV untuk meter air

                $cleanFloor = trim(str_ireplace(['/ CAR', '/CAR', 'CAR', '/'], '', $wFloorRaw));
                $unitKey    = $cleanFloor . '_' . $wUnit;

                $wAwal      = (float) $wRow[3];
                $wAkhir     = (float) $wRow[4];
                $wPakai     = (float) $wRow[5];
                $wKet       = isset($wRow[6]) ? trim($wRow[6]) : null;

                if ($wPakai < 0 || ($wAkhir < $wAwal)) {
                    $wPakai = 0.00;
                    $wKet = $wKet ?: 'Meter Error / Stand Mundur';
                }

                $waterData[$unitKey] = [
                    'floor'      => $cleanFloor,
                    'unit'       => $wUnit,
                    'awal'       => $wAwal,
                    'akhir'      => $wAkhir,
                    'pemakaian'  => $wPakai,
                    'keterangan' => $wKet,
                ];
            }
        }

        DB::beginTransaction();
        try {
            $validTenantIds = [];

            // A. Simpan / Perbarui Master Tenant dan Meter Listrik
            foreach ($unitsData as $key => $u) {
                $hasEv  = isset($evRows[$key]);
                $evInfo = $hasEv ? $evRows[$key] : null;

                $kodeTenant = 'TNT-' . $u['floor'] . '-' . str_replace(' ', '', $u['unit']);
                $namaTenant = 'Unit ' . $u['floor'] . ' ' . $u['unit'];

                $tenant = Tenant::updateOrCreate(
                    [
                        'lantai'     => $u['floor'],
                        'nomor_unit' => $u['unit'],
                    ],
                    [
                        'nama_tenant'         => $namaTenant,
                        'kode_tenant'         => $kodeTenant,
                        'daya_listrik'        => $u['daya'],
                        'daya_listrik_va'     => (int) round($u['daya'] * 1000),
                        'has_ev_charger'      => $hasEv,
                        'daya_ev'             => $hasEv ? $evInfo['daya'] : null,
                        'nomor_meter_listrik' => 'KWH-' . $u['floor'] . '-' . $u['unit'],
                        'nomor_meter_ev'      => $hasEv ? 'KWH-' . $u['floor'] . '-' . $u['unit'] . '-EV' : null,
                        'nomor_meter_air'     => 'PAM-' . $u['floor'] . '-' . $u['unit'],
                        'faktor_kali_listrik' => 1.0000,
                        'faktor_kali_air'     => 1.0000,
                        'is_active'           => true,
                    ]
                );

                $validTenantIds[] = $tenant->id;

                // 1. Simpan Reading Listrik Regular
                TenantEnergyReading::updateOrCreate(
                    [
                        'tenant_id'     => $tenant->id,
                        'periode_tahun' => 2026,
                        'periode_bulan' => 10,
                        'meter_type'    => 'regular',
                    ],
                    [
                        'tanggal_catat' => '2026-10-31',
                        'daya'          => $u['daya'],
                        'stand_awal'    => $u['awal'],
                        'stand_akhir'   => $u['akhir'],
                        'pemakaian'     => $u['pemakaian'],
                        'tarif_per_kwh' => 1699.53,
                        'catatan'       => $u['keterangan'] ?: null,
                        'keterangan'    => $u['keterangan'] ?: null,
                        'is_special_case' => !empty($u['keterangan']),
                        'user_id'       => $adminId,
                        'verified_at'   => now(),
                        'verified_by'   => $adminId,
                    ]
                );

                // 2. Simpan Reading Listrik EV jika ada
                if ($hasEv) {
                    TenantEnergyReading::updateOrCreate(
                        [
                            'tenant_id'     => $tenant->id,
                            'periode_tahun' => 2026,
                            'periode_bulan' => 10,
                            'meter_type'    => 'ev',
                        ],
                        [
                            'tanggal_catat' => '2026-10-31',
                            'daya'          => $evInfo['daya'],
                            'stand_awal'    => $evInfo['awal'],
                            'stand_akhir'   => $evInfo['akhir'],
                            'pemakaian'     => $evInfo['pemakaian'],
                            'tarif_per_kwh' => 1699.53,
                            'catatan'       => $evInfo['keterangan'] ?: 'Fasilitas EV Charging',
                            'keterangan'    => $evInfo['keterangan'] ?: 'Fasilitas EV Charging',
                            'user_id'       => $adminId,
                            'verified_at'   => now(),
                            'verified_by'   => $adminId,
                        ]
                    );
                }

                // 3. Simpan Reading Air Oktober 2026 jika ada di waterData
                if (isset($waterData[$key])) {
                    $w = $waterData[$key];
                    $isSpecial = !empty($w['keterangan']) || ($w['akhir'] < $w['awal']);

                    TenantWaterReading::updateOrCreate(
                        [
                            'tenant_id'     => $tenant->id,
                            'periode_tahun' => 2026,
                            'periode_bulan' => 10,
                        ],
                        [
                            'tanggal_catat' => '2026-10-31',
                            'stand_awal'    => $w['awal'],
                            'stand_akhir'   => $w['akhir'],
                            'pemakaian'     => $w['pemakaian'],
                            'catatan'       => $w['keterangan'] ?: null,
                            'keterangan'    => $w['keterangan'] ?: null,
                            'is_special_case' => $isSpecial,
                            'user_id'       => $adminId,
                            'verified_at'   => now(),
                            'verified_by'   => $adminId,
                        ]
                    );
                }
            }

            // ==============================================================
            // B. Hapus tenant dummy yang tidak ada di list bulan Oktober 2026
            // ==============================================================
            $deletedCount = Tenant::whereNotIn('id', $validTenantIds)->delete();

            DB::commit();

            echo "Berhasil memproses:\n";
            echo "- " . count($validTenantIds) . " Master Tenant Residence Oktober 2026.\n";
            echo "- " . count($validTenantIds) . " Pembacaan Listrik Reguler + " . count($evRows) . " Pembacaan Listrik EV.\n";
            echo "- " . count($waterData) . " Pembacaan Air Oktober 2026.\n";
            echo "- " . $deletedCount . " Tenant dummy/lama yang tidak ada di list Oktober telah dihapus.\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
