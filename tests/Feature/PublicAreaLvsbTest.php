<?php

namespace Tests\Feature;

use App\Models\PublicAreaLvsbReading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAreaLvsbTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $spv;
    private User $teknisi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->superadmin = User::factory()->create([
            'role'      => 'superadmin',
            'is_active' => true,
        ]);
        $this->spv = User::factory()->create([
            'role'      => 'eng_spv',
            'is_active' => true,
        ]);
        $this->teknisi = User::factory()->create([
            'role'      => 'teknisi',
            'is_active' => true,
        ]);
    }

    private function createFakePng(string $name): UploadedFile
    {
        $base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        return UploadedFile::fake()->createWithContent($name, base64_decode($base64));
    }

    public function test_user_can_access_lvsb_index()
    {
        $res = $this->actingAs($this->teknisi)->get(route('public-area.lvsb.index'));
        $res->assertOk();
        $res->assertSee('Sub Menu: LVSB (Low Voltage Switchboard)');
        $res->assertSee('Incoming From Trafo Apart A/B');
        $res->assertSee('Outgoing - 1.2');
        $res->assertSee('Outgoing - 1.1');
        $res->assertSee('Outgoing - 2.1');
        $res->assertSee('Outgoing - 2.2');
        $res->assertSee('Incoming Capacitor');
        $res->assertSee('Riser 3');
        $res->assertSee('OV AC Sky Lobby');
        $res->assertSee('Ac R. Panel & R. Trafo');
        $res->assertSee('Riser 4');
        $res->assertSee('Riser 2');
        $res->assertSee('DB Fire 2 Level 12');
        $res->assertSee('Spare');
        $res->assertSee('Riser 1');
        $res->assertSee('Riser 5');
    }

    public function test_teknisi_can_store_lvsb_reading_with_photo()
    {
        $file = $this->createFakePng('panel_lvsb.png');
        $today = Carbon::today()->toDateString();

        $payload = [
            'tanggal' => $today,
            'foto'    => $file,
            'catatan' => 'Suhu trafo normal, panel bersih',
            'readings' => [
                'incoming_trafo' => [
                    'voltage_3p'   => 385.2,
                    'current'      => 450.5,
                    'power'        => 280.4,
                    'energy'       => 12.3456,
                    'power_factor' => 0.96,
                    'frequency'    => 50.1,
                ],
                'outgoing_1_2' => [
                    'riser_3' => [
                        'voltage_3p'   => 384.0,
                        'current'      => 120.0,
                        'power'        => 75.0,
                        'energy'       => 3.1234,
                        'power_factor' => 0.95,
                        'frequency'    => 50.0,
                    ],
                ],
                'incoming_capacitor' => [
                    'power_factor' => 0.98,
                ],
            ],
        ];

        $res = $this->actingAs($this->teknisi)->post(route('public-area.lvsb.store'), $payload);
        $res->assertRedirect(route('public-area.lvsb.index'));

        $this->assertNotNull(PublicAreaLvsbReading::whereDate('tanggal', $today)->first());

        $record = PublicAreaLvsbReading::first();
        $this->assertNotNull($record->foto);
        $this->assertEquals(385.2, $record->val('incoming_trafo', 'voltage_3p'));
        $this->assertEquals(0.98, $record->val('incoming_capacitor', 'power_factor'));
        $this->assertEquals(120.0, $record->val('outgoing_1_2', 'riser_3', 'current'));
    }

    public function test_teknisi_can_store_lvsb_reading_with_multiple_group_photos()
    {
        $fotoTrafo = $this->createFakePng('trafo.png');
        $fotoOut12 = $this->createFakePng('outgoing12.png');
        $today = Carbon::today()->toDateString();

        $payload = [
            'tanggal' => $today,
            'fotos'   => [
                'incoming_trafo' => $fotoTrafo,
                'outgoing_1_2'   => $fotoOut12,
            ],
            'catatan' => 'Semua grup panel dicek dan difoto per panel',
            'readings' => [
                'incoming_trafo' => [
                    'voltage_3p' => 382.5,
                ],
                'outgoing_1_2' => [
                    'riser_3' => [
                        'current' => 110.5,
                    ],
                ],
            ],
        ];

        $res = $this->actingAs($this->teknisi)->post(route('public-area.lvsb.store'), $payload);
        $res->assertRedirect(route('public-area.lvsb.index'));

        $record = PublicAreaLvsbReading::whereDate('tanggal', $today)->first();
        $this->assertNotNull($record);
        $this->assertNotNull($record->getFoto('incoming_trafo'));
        $this->assertNotNull($record->getFoto('outgoing_1_2'));
        $this->assertNull($record->getFoto('incoming_capacitor'));
        $this->assertCount(2, $record->getAllFotos());
        Storage::disk('public')->assertExists($record->getFoto('incoming_trafo'));
        Storage::disk('public')->assertExists($record->getFoto('outgoing_1_2'));
    }

    public function test_offline_sync_via_json_response()
    {
        $today = Carbon::today()->toDateString();

        $payload = [
            'tanggal' => $today,
            'readings' => [
                'incoming_trafo' => [
                    'voltage_3p' => 380.0,
                    'power'      => 250.0,
                ],
                'incoming_capacitor' => [
                    'power_factor' => 0.99,
                ],
            ],
        ];

        $res = $this->actingAs($this->teknisi)
            ->withHeaders(['Accept' => 'application/json', 'X-Offline-Sync' => 'true'])
            ->post(route('public-area.lvsb.store'), $payload);

        $res->assertOk();
        $res->assertJsonPath('success', true);
        $res->assertJsonStructure(['success', 'message', 'data']);

        $this->assertNotNull(PublicAreaLvsbReading::whereDate('tanggal', $today)->first());
    }

    public function test_management_can_verify_and_delete_lvsb()
    {
        $reading = PublicAreaLvsbReading::create([
            'tanggal'  => Carbon::today(),
            'readings' => ['incoming_capacitor' => ['power_factor' => 0.95]],
            'user_id'  => $this->teknisi->id,
        ]);

        // Verifikasi oleh SPV
        $res = $this->actingAs($this->spv)->post(route('public-area.lvsb.verify', $reading));
        $res->assertRedirect();
        $reading->refresh();
        $this->assertNotNull($reading->verified_at);
        $this->assertEquals($this->spv->id, $reading->verified_by);

        // Hapus oleh Super Admin
        $resDel = $this->actingAs($this->superadmin)->delete(route('public-area.lvsb.destroy', $reading));
        $resDel->assertRedirect(route('public-area.lvsb.index'));
        $this->assertDatabaseMissing('public_area_lvsb_readings', ['id' => $reading->id]);
    }

    public function test_management_can_export_lvsb_excel_and_pdf()
    {
        PublicAreaLvsbReading::create([
            'tanggal'  => Carbon::today(),
            'readings' => [
                'incoming_trafo' => ['voltage_3p' => 380, 'power' => 200],
                'incoming_capacitor' => ['power_factor' => 0.97],
            ],
            'user_id'  => $this->teknisi->id,
        ]);

        $resExcel = $this->actingAs($this->spv)->get(route('public-area.lvsb.excel'));
        $resExcel->assertOk();
        $resExcel->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        $resPdf = $this->actingAs($this->spv)->get(route('public-area.lvsb.pdf'));
        $resPdf->assertOk();
        $resPdf->assertSee('LOGSHEET LVSB');
    }

    public function test_old_electricity_route_redirects_to_lvsb()
    {
        $res = $this->actingAs($this->teknisi)->get('/logsheet-public-area/electricity');
        $res->assertRedirect('/logsheet-public-area/lvsb');
    }

    public function test_superadmin_can_access_and_store_manual_lvsb_back_date()
    {
        $pastDate = Carbon::today()->subDays(7)->toDateString();

        $pageRes = $this->actingAs($this->superadmin)->get(route('public-area.manual.lvsb', ['tanggal' => $pastDate]));
        $pageRes->assertOk();
        $pageRes->assertSee('Input Manual Logsheet LVSB (Back Date)');

        $storeRes = $this->actingAs($this->superadmin)->post(route('public-area.manual.lvsb.store'), [
            'tanggal' => $pastDate,
            'catatan' => 'Back date LVSB catatan',
            'readings' => [
                'incoming_trafo' => [
                    'voltage_3p'   => 382.5,
                    'current'      => 410.0,
                    'power'        => 260.0,
                    'energy'       => 10.5000,
                    'power_factor' => 0.95,
                    'frequency'    => 50.0,
                ],
                'incoming_capacitor' => [
                    'power_factor' => 0.98,
                ],
            ],
        ]);
        $storeRes->assertRedirect(route('public-area.manual.lvsb', ['tanggal' => $pastDate]));

        $this->assertTrue(
            PublicAreaLvsbReading::whereDate('tanggal', $pastDate)
                ->where('catatan', 'Back date LVSB catatan')
                ->exists()
        );
    }

    public function test_non_superadmin_cannot_access_manual_lvsb_back_date()
    {
        $res = $this->actingAs($this->teknisi)->get(route('public-area.manual.lvsb'));
        $res->assertForbidden();
    }

    public function test_lvsb_export_with_custom_date_interval()
    {
        $startDate = Carbon::today()->subMonths(3)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $resExcel = $this->actingAs($this->spv)->get(route('public-area.lvsb.excel', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]));
        $resExcel->assertOk();
        $resExcel->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        $resPdf = $this->actingAs($this->spv)->get(route('public-area.lvsb.pdf', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]));
        $resPdf->assertOk();
        $resPdf->assertSee('LOGSHEET LVSB');
    }

    public function test_lvsb_accepts_and_displays_8_decimal_digits_for_energy()
    {
        $today = Carbon::today()->toDateString();
        $energyValue = 12.12345678;

        $res = $this->actingAs($this->teknisi)->post(route('public-area.lvsb.store'), [
            'tanggal'   => $today,
            'group_key' => 'incoming_trafo',
            'readings'  => [
                'incoming_trafo' => [
                    'voltage_3p'   => 380.0,
                    'current'      => 100.0,
                    'power'        => 50.0,
                    'energy'       => $energyValue,
                    'power_factor' => 0.95,
                    'frequency'    => 50.0,
                ],
            ],
        ]);
        $res->assertRedirect(route('public-area.lvsb.index'));

        $record = PublicAreaLvsbReading::whereDate('tanggal', $today)->first();
        $this->assertNotNull($record);
        $this->assertEquals($energyValue, $record->val('incoming_trafo', 'energy'));

        $pageRes = $this->actingAs($this->teknisi)->get(route('public-area.lvsb.index'));
        $pageRes->assertOk();
        $pageRes->assertSee('12,12345678');
    }
}
