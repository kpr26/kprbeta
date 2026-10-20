<?php

namespace Tests\Feature;

use App\Models\PublicAreaMvsbReading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAreaMvsbTest extends TestCase
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

    public function test_user_can_access_mvsb_index_and_sees_sidebar_item()
    {
        $res = $this->actingAs($this->teknisi)->get(route('public-area.mvsb.index'));
        $res->assertOk();
        $res->assertSee('Sub Menu: MVSB Incoming From PLN');
        $res->assertSee('MVSB Incoming From PLN');
        $res->assertSee('Voltage 20kV');
        $res->assertSee('Current');
    }

    public function test_teknisi_can_store_mvsb_reading_with_photo()
    {
        $file = $this->createFakePng('panel_mvsb.png');
        $today = Carbon::today()->toDateString();

        $payload = [
            'tanggal'      => $today,
            'voltage_20kv' => 20150.5,
            'current'      => 32.45,
            'foto'         => $file,
            'catatan'      => 'Tegangan gardu incoming PLN normal',
        ];

        $res = $this->actingAs($this->teknisi)->post(route('public-area.mvsb.store'), $payload);
        $res->assertRedirect(route('public-area.mvsb.index'));

        $record = PublicAreaMvsbReading::whereDate('tanggal', $today)->first();
        $this->assertNotNull($record);
        $this->assertEquals(20150.5, $record->voltage_20kv);
        $this->assertEquals(32.45, $record->current);
        $this->assertNotNull($record->foto);
        Storage::disk('public')->assertExists($record->foto);
    }

    public function test_offline_sync_via_json_response()
    {
        $today = Carbon::today()->toDateString();

        $payload = [
            'tanggal'      => $today,
            'voltage_20kv' => 19980.0,
            'current'      => 28.10,
        ];

        $res = $this->actingAs($this->teknisi)
            ->withHeaders(['Accept' => 'application/json', 'X-Offline-Sync' => 'true'])
            ->post(route('public-area.mvsb.store'), $payload);

        $res->assertOk();
        $res->assertJsonPath('success', true);
        $res->assertJsonStructure(['success', 'message', 'data']);

        $this->assertNotNull(PublicAreaMvsbReading::whereDate('tanggal', $today)->first());
    }

    public function test_prev_reading_shows_correctly()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        PublicAreaMvsbReading::create([
            'tanggal'      => $yesterday,
            'voltage_20kv' => 20050.0,
            'current'      => 26.50,
            'user_id'      => $this->teknisi->id,
        ]);

        $res = $this->actingAs($this->teknisi)->get(route('public-area.mvsb.index'));
        $res->assertOk();
        $res->assertSee('20.050,0 Volt');
        $res->assertSee('26,50 Ampere');
    }

    public function test_management_can_verify_and_delete_mvsb()
    {
        $reading = PublicAreaMvsbReading::create([
            'tanggal'      => Carbon::today(),
            'voltage_20kv' => 20100.0,
            'current'      => 30.00,
            'user_id'      => $this->teknisi->id,
        ]);

        // Verifikasi oleh SPV
        $res = $this->actingAs($this->spv)->post(route('public-area.mvsb.verify', $reading));
        $res->assertRedirect();
        $reading->refresh();
        $this->assertNotNull($reading->verified_at);
        $this->assertEquals($this->spv->id, $reading->verified_by);

        // Hapus oleh Super Admin
        $resDel = $this->actingAs($this->superadmin)->delete(route('public-area.mvsb.destroy', $reading));
        $resDel->assertRedirect(route('public-area.mvsb.index'));
        $this->assertDatabaseMissing('public_area_mvsb_readings', ['id' => $reading->id]);
    }

    public function test_management_can_export_mvsb_excel_and_pdf()
    {
        PublicAreaMvsbReading::create([
            'tanggal'      => Carbon::today(),
            'voltage_20kv' => 20000.0,
            'current'      => 25.00,
            'user_id'      => $this->teknisi->id,
        ]);

        $resExcel = $this->actingAs($this->spv)->get(route('public-area.mvsb.excel'));
        $resExcel->assertOk();
        $resExcel->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        $resPdf = $this->actingAs($this->spv)->get(route('public-area.mvsb.pdf'));
        $resPdf->assertOk();
        $resPdf->assertSee('LOGSHEET MVSB INCOMING FROM PLN');
    }

    public function test_hub_displays_mvsb_card()
    {
        $res = $this->actingAs($this->teknisi)->get(route('public-area.index'));
        $res->assertOk();
        $res->assertSee('MVSB Incoming From PLN');
        $res->assertSee('Buka Sub Menu MVSB');
    }
}
