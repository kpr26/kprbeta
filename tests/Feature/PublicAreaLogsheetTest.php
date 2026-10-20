<?php

namespace Tests\Feature;

use App\Models\PublicAreaMeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAreaLogsheetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createFakePng(string $name): UploadedFile
    {
        $base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        return UploadedFile::fake()->createWithContent($name, base64_decode($base64));
    }

    public function test_user_can_access_public_area_hub(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($teknisi)->get(route('public-area.index'));
        $response->assertStatus(200);
        $response->assertSee('Menu Logsheet Public Area');
        $response->assertSee('Water Meter Public Area');
        $response->assertSee('KWh Meter Public Area');
        $response->assertSee('Cooling Tower 12A');
        $response->assertSee('Toilet Sky Lobby 11 &amp; Mushola 12', false);
        $response->assertSee('Pantry Office');
    }

    public function test_water_index_shows_three_separate_meter_cards(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($teknisi)->get(route('public-area.water.index'));
        $response->assertStatus(200);
        $response->assertSee('Water Meter Cooling Tower 12A');
        $response->assertSee('Water Meter Toilet Sky Lobby 11 &amp; Mushola 12', false);
        $response->assertSee('Water Meter Pantry Office');
        $response->assertSee('Mode Offline Aktif');
    }

    public function test_teknisi_can_input_three_water_meters_separately_with_photos(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        // 1. Input Cooling Tower 12A
        $file1 = $this->createFakePng('ct_meter.png');
        $res1 = $this->actingAs($teknisi)->post(route('public-area.water.store'), [
            'meter_key'   => 'cooling_tower',
            'stand_meter' => 150.75,
            'foto'        => $file1,
            'catatan'     => 'Kondisi CT normal',
        ]);
        $res1->assertRedirect(route('public-area.water.index'));

        // 2. Input Toilet Sky Lobby 11 & Mushola 12
        $file2 = $this->createFakePng('toilet_meter.png');
        $res2 = $this->actingAs($teknisi)->post(route('public-area.water.store'), [
            'meter_key'   => 'toilet_sky_lobby',
            'stand_meter' => 84.20,
            'foto'        => $file2,
            'catatan'     => 'Kondisi toilet & mushola bersih',
        ]);
        $res2->assertRedirect(route('public-area.water.index'));

        // 3. Input Pantry Office
        $file3 = $this->createFakePng('pantry_meter.png');
        $res3 = $this->actingAs($teknisi)->post(route('public-area.water.store'), [
            'meter_key'   => 'pantry_office',
            'stand_meter' => 45.10,
            'foto'        => $file3,
            'catatan'     => 'Tekanan air pantry lancar',
        ]);
        $res3->assertRedirect(route('public-area.water.index'));

        // Verifikasi ketiga data tersimpan secara terpisah
        $this->assertDatabaseHas('public_area_meter_readings', [
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'stand_meter' => 150.75,
        ]);
        $this->assertDatabaseHas('public_area_meter_readings', [
            'category'    => 'water',
            'meter_key'   => 'toilet_sky_lobby',
            'stand_meter' => 84.20,
        ]);
        $this->assertDatabaseHas('public_area_meter_readings', [
            'category'    => 'water',
            'meter_key'   => 'pantry_office',
            'stand_meter' => 45.10,
        ]);

        $ctReading = PublicAreaMeterReading::where('meter_key', 'cooling_tower')->first();
        $this->assertNotNull($ctReading->foto);
        Storage::disk('public')->assertExists($ctReading->foto);
    }

    public function test_water_meter_calculates_daily_usage_and_accumulative(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $d1 = now()->startOfMonth()->format('Y-m-d');
        $d2 = now()->startOfMonth()->addDay()->format('Y-m-d');

        // Hari 1
        PublicAreaMeterReading::create([
            'tanggal'     => $d1,
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Water Meter Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.00,
            'user_id'     => $admin->id,
        ]);

        // Hari 2
        PublicAreaMeterReading::create([
            'tanggal'     => $d2,
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Water Meter Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 125.50,
            'user_id'     => $admin->id,
        ]);

        $year = (int) now()->year;
        $month = (int) now()->month;
        $rows = PublicAreaMeterReading::monthWithUsage($year, $month, 'water');

        $rowDay2 = $rows->first(fn ($r) => $r->tanggal->toDateString() === $d2 && $r->meter_key === 'cooling_tower');
        $this->assertNotNull($rowDay2);
        $this->assertEquals(25.50, $rowDay2->pemakaian);
        $this->assertEquals(25.50, $rowDay2->cum_usage);
    }

    public function test_teknisi_can_input_kwh_meter_public_area(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $file = $this->createFakePng('kwh_pa.png');

        $response = $this->actingAs($teknisi)->post(route('public-area.electricity.store'), [
            'stand_meter' => 8420.50,
            'foto'        => $file,
            'catatan'     => 'Panel LP normal',
        ]);

        $response->assertRedirect(route('public-area.electricity.index'));

        $this->assertDatabaseHas('public_area_meter_readings', [
            'category'    => 'electricity',
            'meter_key'   => 'kwh_public_area',
            'stand_meter' => 8420.50,
        ]);

        $reading = PublicAreaMeterReading::where('category', 'electricity')->first();
        $this->assertNotNull($reading->foto);
        Storage::disk('public')->assertExists($reading->foto);
    }

    public function test_offline_sync_via_json_response(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $file = $this->createFakePng('ct_offline.png');

        // Simulasi request dari offline-logsheet.js saat HP kembali mendapatkan sinyal
        $response = $this->actingAs($teknisi)->postJson(route('public-area.water.store'), [
            'meter_key'          => 'cooling_tower',
            'stand_meter'        => 200.00,
            'foto'               => $file,
            'catatan'            => 'Disinkronkan dari mode offline',
            'tanggal_pencatatan' => today()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('public_area_meter_readings', [
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'stand_meter' => 200.00,
        ]);
    }

    public function test_offline_sync_validates_stand_and_returns_json_error(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        // Data awal
        PublicAreaMeterReading::create([
            'tanggal'     => now()->subDay()->format('Y-m-d'),
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Water Meter Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 300.00,
            'user_id'     => $teknisi->id,
        ]);

        // Kirim stand yang lebih kecil dari stand kemarin
        $file = $this->createFakePng('ct_err.png');
        $response = $this->actingAs($teknisi)->postJson(route('public-area.water.store'), [
            'meter_key'          => 'cooling_tower',
            'stand_meter'        => 280.00,
            'foto'               => $file,
            'tanggal_pencatatan' => today()->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Stand tidak boleh lebih kecil dari stand sebelumnya (300,00 m³).',
        ]);
    }

    public function test_management_can_verify_and_delete_public_area_reading(): void
    {
        $spv = User::factory()->create(['role' => 'eng_spv']);
        $file = $this->createFakePng('to_delete.png');

        $reading = PublicAreaMeterReading::create([
            'tanggal'     => today()->format('Y-m-d'),
            'category'    => 'water',
            'meter_key'   => 'pantry_office',
            'meter_name'  => 'Water Meter Pantry Office',
            'unit'        => 'm³',
            'stand_meter' => 50.00,
            'foto'        => $file->store('public_area/water', 'public'),
            'user_id'     => $spv->id,
        ]);

        // Verifikasi
        $verRes = $this->actingAs($spv)->post(route('public-area.verify', $reading));
        $verRes->assertRedirect();
        $this->assertNotNull($reading->fresh()->verified_at);

        // Hapus
        $delRes = $this->actingAs($spv)->delete(route('public-area.destroy', $reading));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('public_area_meter_readings', ['id' => $reading->id]);
        Storage::disk('public')->assertMissing($reading->foto);
    }

    public function test_management_can_export_excel_and_pdf(): void
    {
        $engAdmin = User::factory()->create(['role' => 'eng_admin']);

        PublicAreaMeterReading::create([
            'tanggal'     => today()->format('Y-m-d'),
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Water Meter Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.00,
            'user_id'     => $engAdmin->id,
        ]);

        $excelRes = $this->actingAs($engAdmin)->get(route('public-area.excel', ['category' => 'water']));
        $excelRes->assertStatus(200);
        $excelRes->assertHeader('content-disposition');

        $pdfRes = $this->actingAs($engAdmin)->get(route('public-area.pdf', ['category' => 'water']));
        $pdfRes->assertStatus(200);
        $pdfRes->assertSee('LOGSHEET REKAPITULASI WATER METER PUBLIC AREA');
    }
}
