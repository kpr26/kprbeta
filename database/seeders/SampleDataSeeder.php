<?php

namespace Database\Seeders;

use App\Models\MeterReading;
use App\Models\PublicAreaLvsbReading;
use App\Models\PublicAreaMeterReading;
use App\Models\PublicAreaMvsbReading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teknisi = User::where('role', 'teknisi')->first() ?? User::first();
        $admin   = User::where('role', 'admin')->first() ?? User::first();

        if (! $teknisi || ! $admin) {
            $this->command->warn('User teknisi atau admin belum tersedia. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        // Tanggal awal: 90 hari yang lalu s/d hari ini (26 Oktober 2026)
        $endDate   = Carbon::create(2026, 10, 26);
        $startDate = $endDate->copy()->subDays(92);
        $daysCount = $startDate->diffInDays($endDate);

        $this->command->info("Men-generate data sampel dari {$startDate->toDateString()} s/d {$endDate->toDateString()} ({$daysCount} hari)...");

        // Water Public Area baseline
        $standCT    = 14200.00;
        $standSL    = 4500.00;
        $standPO    = 1980.00;
        $standKwhPA = 56200.00;

        // Gedung Utama baseline
        $standLwbp      = 184500.00;
        $standWbp       = 52300.00;
        $standAirGedung = 34800.00;

        for ($i = 0; $i <= $daysCount; $i++) {
            $curDate = $startDate->copy()->addDays($i);
            $dStr    = $curDate->toDateString();
            $isToday = $curDate->isToday();

            // 1. Water CT (Cooling Tower 12A)
            $usageCT = round(10.5 + 4.5 * sin($i * 0.2) + rand(-10, 15) * 0.1, 2);
            $standCT += $usageCT;
            PublicAreaMeterReading::updateOrCreate(
                ['tanggal' => $dStr, 'category' => 'water', 'meter_key' => 'cooling_tower'],
                [
                    'meter_name'  => 'Water Meter Cooling Tower 12A',
                    'unit'        => 'm³',
                    'stand_meter' => round($standCT, 2),
                    'user_id'     => $teknisi->id,
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );

            // 2. Water Toilet & Mushola Sky Lobby
            $usageSL = round(4.2 + 1.8 * sin($i * 0.15) + rand(-5, 8) * 0.1, 2);
            $standSL += $usageSL;
            PublicAreaMeterReading::updateOrCreate(
                ['tanggal' => $dStr, 'category' => 'water', 'meter_key' => 'toilet_sky_lobby'],
                [
                    'meter_name'  => 'Water Meter Toilet Sky Lobby 11 & Mushola 12',
                    'unit'        => 'm³',
                    'stand_meter' => round($standSL, 2),
                    'user_id'     => $teknisi->id,
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );

            // 3. Water Pantry Office
            $usagePO = round(2.3 + 0.8 * sin($i * 0.18) + rand(-3, 5) * 0.1, 2);
            $standPO += $usagePO;
            PublicAreaMeterReading::updateOrCreate(
                ['tanggal' => $dStr, 'category' => 'water', 'meter_key' => 'pantry_office'],
                [
                    'meter_name'  => 'Water Meter Pantry Office',
                    'unit'        => 'm³',
                    'stand_meter' => round($standPO, 2),
                    'user_id'     => $teknisi->id,
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );

            // 4. KWh Public Area (Listrik)
            $usageKwhPA = round(165.0 + 35.0 * sin($i * 0.22) + rand(-15, 20), 2);
            $standKwhPA += $usageKwhPA;
            PublicAreaMeterReading::updateOrCreate(
                ['tanggal' => $dStr, 'category' => 'electricity', 'meter_key' => 'kwh_public_area'],
                [
                    'meter_name'  => 'KWh Meter Public Area',
                    'unit'        => 'kWh',
                    'stand_meter' => round($standKwhPA, 2),
                    'user_id'     => $teknisi->id,
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );

            // 5. LVSB (Low Voltage Switchboard)
            $volt3p      = round(382.5 + sin($i * 0.12) * 4.0 + rand(-15, 15) * 0.1, 1);
            $currentLvsb = round(265.0 + sin($i * 0.25) * 28.0 + rand(-8, 8), 2);
            $powerLvsb   = round(168.0 + sin($i * 0.25) * 22.0 + rand(-5, 5), 2);
            $energyLvsb  = round(1.2400 + ($i * 0.0038), 4);
            $pfLvsb      = round(0.95 + rand(-2, 3) * 0.01, 2);

            PublicAreaLvsbReading::updateOrCreate(
                ['tanggal' => $dStr],
                [
                    'readings' => [
                        'incoming_trafo' => [
                            'voltage_3p'   => $volt3p,
                            'current'      => $currentLvsb,
                            'power'        => $powerLvsb,
                            'energy'       => $energyLvsb,
                            'power_factor' => $pfLvsb,
                            'frequency'    => 50.0,
                        ],
                        'outgoing_1_2' => [
                            'riser_3'          => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.28, 2), 'power' => round($powerLvsb * 0.28, 2), 'energy' => round($energyLvsb * 0.28, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                            'ov_ac_sky_lobby'  => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.24, 2), 'power' => round($powerLvsb * 0.24, 2), 'energy' => round($energyLvsb * 0.24, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                            'ac_r_panel_trafo' => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.15, 2), 'power' => round($powerLvsb * 0.15, 2), 'energy' => round($energyLvsb * 0.15, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                        ],
                        'outgoing_1_1' => [
                            'riser_4' => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.32, 2), 'power' => round($powerLvsb * 0.32, 2), 'energy' => round($energyLvsb * 0.32, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                        ],
                        'outgoing_2_1' => [
                            'riser_2'            => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.26, 2), 'power' => round($powerLvsb * 0.26, 2), 'energy' => round($energyLvsb * 0.26, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                            'db_fire_2_level_12' => ['voltage_3p' => $volt3p, 'current' => 12.5, 'power' => 7.8, 'energy' => 0.05, 'power_factor' => 0.96, 'frequency' => 50.0],
                            'spare'              => ['voltage_3p' => $volt3p, 'current' => 0.0, 'power' => 0.0, 'energy' => 0.0, 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                        ],
                        'outgoing_2_2' => [
                            'riser_1' => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.25, 2), 'power' => round($powerLvsb * 0.25, 2), 'energy' => round($energyLvsb * 0.25, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                            'riser_5' => ['voltage_3p' => $volt3p, 'current' => round($currentLvsb * 0.22, 2), 'power' => round($powerLvsb * 0.22, 2), 'energy' => round($energyLvsb * 0.22, 4), 'power_factor' => $pfLvsb, 'frequency' => 50.0],
                        ],
                        'incoming_capacitor' => [
                            'power_factor' => round(0.96 + rand(-2, 2) * 0.01, 2),
                        ],
                    ],
                    'user_id'     => $teknisi->id,
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );

            // 6. MVSB (Medium Voltage Switchboard 20kV)
            $voltMvsb = round(20120 + sin($i * 0.15) * 120 + rand(-40, 40), 1);
            $currMvsb = round(32.8 + sin($i * 0.25) * 3.5 + rand(-15, 15) * 0.1, 2);
            PublicAreaMvsbReading::updateOrCreate(
                ['tanggal' => $dStr],
                [
                    'voltage_20kv' => $voltMvsb,
                    'current'      => $currMvsb,
                    'user_id'      => $teknisi->id,
                    'verified_at'  => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by'  => $isToday ? null : $admin->id,
                ]
            );

            // 7. Gedung Utama AMR PLN & Water Meter
            $uLwbp = round(1280.0 + sin($i * 0.2) * 180 + rand(-50, 50), 2);
            $uWbp  = round(380.0 + sin($i * 0.25) * 70 + rand(-20, 20), 2);
            $uAir  = round(82.0 + sin($i * 0.15) * 18 + rand(-8, 8), 2);

            $standLwbp += $uLwbp;
            $standWbp  += $uWbp;
            $standAirGedung += $uAir;

            MeterReading::updateOrCreate(
                ['tanggal' => $dStr],
                [
                    'user_id'     => $teknisi->id,
                    'stand_lwbp'  => round($standLwbp, 2),
                    'stand_wbp'   => round($standWbp, 2),
                    'stand_total' => round($standLwbp + $standWbp, 2),
                    'stand_bp'    => round($standWbp, 2),
                    'stand_lbp1'  => round($standLwbp * 0.6, 2),
                    'stand_lbp2'  => round($standLwbp * 0.4, 2),
                    'stand_air'   => round($standAirGedung, 2),
                    'verified_at' => $isToday ? null : $curDate->copy()->addHours(18),
                    'verified_by' => $isToday ? null : $admin->id,
                ]
            );
        }

        $this->command->info("Data sampel utilitas berhasil digenerate!");
    }
}
