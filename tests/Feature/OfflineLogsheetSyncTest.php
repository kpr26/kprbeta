<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfflineLogsheetSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function createFakePng(string $filename = 'test.png'): UploadedFile
    {
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'test_img');
        file_put_contents($tmp, $pngBytes);

        return new UploadedFile($tmp, $filename, 'image/png', null, true);
    }

    public function test_teknisi_can_submit_listrik_with_json_response_for_offline_sync(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $file = $this->createFakePng('meter_kwh.png');

        $response = $this->actingAs($teknisi)->postJson(route('logsheet.store.listrik'), [
            'stand_lwbp'         => 1250.50,
            'stand_wbp'          => 450.25,
            'foto_kwh'           => $file,
            'catatan'            => 'Pencatatan offline tersinkron',
            'tanggal_pencatatan' => today()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type'    => 'listrik',
        ]);

        $reading = MeterReading::whereDate('tanggal', today())->first();
        $this->assertNotNull($reading);
        $this->assertEquals(1250.50, $reading->stand_lwbp);
        $this->assertEquals(450.25, $reading->stand_wbp);
        $this->assertEquals($teknisi->id, $reading->user_id);
    }

    public function test_teknisi_can_submit_air_with_json_response_for_offline_sync(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $file = $this->createFakePng('meter_air.png');

        $response = $this->actingAs($teknisi)->postJson(route('logsheet.store.air'), [
            'stand_air'          => 820.75,
            'foto_air'           => $file,
            'catatan'            => 'Pencatatan air offline tersinkron',
            'tanggal_pencatatan' => today()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type'    => 'air',
        ]);

        $reading = MeterReading::whereDate('tanggal', today())->first();
        $this->assertNotNull($reading);
        $this->assertEquals(820.75, $reading->stand_air);
        $this->assertEquals($teknisi->id, $reading->user_id);
    }

    public function test_offline_sync_validates_stand_and_returns_json_error(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        // Catatan kemarin
        MeterReading::create([
            'tanggal'    => today()->subDay()->toDateString(),
            'stand_lwbp' => 1000.00,
            'stand_wbp'  => 500.00,
            'stand_air'  => 300.00,
            'user_id'    => $teknisi->id,
        ]);

        $file = $this->createFakePng('kwh.png');

        // Percobaan submit stand lebih kecil dari kemarin via JSON
        $response = $this->actingAs($teknisi)->postJson(route('logsheet.store.listrik'), [
            'stand_lwbp' => 900.00, // Lebih kecil dari 1000.00
            'stand_wbp'  => 550.00,
            'foto_kwh'   => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_teknisi_cannot_overwrite_existing_logsheet_via_json(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        // Sudah tercatat hari ini
        MeterReading::create([
            'tanggal'    => today()->toDateString(),
            'stand_lwbp' => 1100.00,
            'stand_wbp'  => 400.00,
            'foto_kwh'   => 'logsheet/existing_kwh.jpg',
            'user_id'    => $teknisi->id,
        ]);

        $file = $this->createFakePng('kwh_new.png');

        $response = $this->actingAs($teknisi)->postJson(route('logsheet.store.listrik'), [
            'stand_lwbp' => 1150.00,
            'stand_wbp'  => 420.00,
            'foto_kwh'   => $file,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_super_admin_can_delete_logsheet_with_null_photos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reading = MeterReading::create([
            'tanggal'    => '2026-05-01',
            'stand_lwbp' => 10,
            'stand_wbp'  => 5,
            'user_id'    => $admin->id,
            'foto_kwh'   => null,
            'foto_air'   => null,
        ]);

        $response = $this->actingAs($admin)->delete(route('logsheet.destroy', $reading));
        $response->assertRedirect(route('logsheet.index'));
        $this->assertDatabaseMissing('meter_readings', ['id' => $reading->id]);
    }

    public function test_super_admin_can_delete_logsheet_with_existing_photos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('logsheet/test_kwh.jpg', 'fake-image');
        Storage::disk('public')->put('logsheet/test_air.jpg', 'fake-image');

        $reading = MeterReading::create([
            'tanggal'    => '2026-05-02',
            'stand_lwbp' => 20,
            'stand_wbp'  => 10,
            'user_id'    => $admin->id,
            'foto_kwh'   => 'logsheet/test_kwh.jpg',
            'foto_air'   => 'logsheet/test_air.jpg',
        ]);

        $response = $this->actingAs($admin)->delete(route('logsheet.destroy', $reading));
        $response->assertRedirect(route('logsheet.index'));
        $this->assertDatabaseMissing('meter_readings', ['id' => $reading->id]);
        Storage::disk('public')->assertMissing('logsheet/test_kwh.jpg');
        Storage::disk('public')->assertMissing('logsheet/test_air.jpg');
    }

    public function test_accessing_deleted_logsheet_redirects_to_index_with_warning(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/logsheet/999999');

        $response->assertRedirect(route('logsheet.index'));
        $response->assertSessionHas('warning', 'Data logsheet tidak ditemukan atau telah dihapus sebelumnya.');
    }
}
