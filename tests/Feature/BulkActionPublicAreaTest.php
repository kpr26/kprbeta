<?php

namespace Tests\Feature;

use App\Models\PublicAreaLvsbReading;
use App\Models\PublicAreaMeterReading;
use App\Models\PublicAreaMvsbReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkActionPublicAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_superadmin_sees_bulk_actions_toolbar_and_checkboxes_on_water_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 120.5,
            'user_id'     => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->get(route('public-area.water.index', [
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('bulk-master-water');
        $response->assertSee('btn-trigger-bulk-verify-water');
        $response->assertSee('btn-trigger-bulk-delete-water');
        $response->assertSee('bulk-item-water');
    }

    public function test_superadmin_sees_bulk_actions_toolbar_and_checkboxes_on_lvsb_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        PublicAreaLvsbReading::create([
            'tanggal'  => '2026-10-01',
            'user_id'  => $teknisi->id,
            'readings' => ['incoming_trafo' => ['voltage_3p' => 380]],
        ]);

        $response = $this->actingAs($admin)->get(route('public-area.lvsb.index', [
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('bulk-master-lvsb');
        $response->assertSee('btn-trigger-bulk-verify-lvsb');
        $response->assertSee('btn-trigger-bulk-delete-lvsb');
        $response->assertSee('bulk-item-lvsb');
    }

    public function test_superadmin_sees_bulk_actions_toolbar_and_checkboxes_on_mvsb_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        PublicAreaMvsbReading::create([
            'tanggal'      => '2026-10-01',
            'voltage_20kv' => 20000,
            'current'      => 30,
            'user_id'      => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->get(route('public-area.mvsb.index', [
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('bulk-master-mvsb');
        $response->assertSee('btn-trigger-bulk-verify-mvsb');
        $response->assertSee('btn-trigger-bulk-delete-mvsb');
        $response->assertSee('bulk-item-mvsb');
    }

    public function test_non_superadmin_does_not_see_bulk_actions_toolbar_on_public_area_pages(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 120.5,
            'user_id'     => $teknisi->id,
        ]);

        $respWater = $this->actingAs($teknisi)->get(route('public-area.water.index'));
        $respWater->assertStatus(200);
        $respWater->assertDontSee('bulk-master-water');
        $respWater->assertDontSee('btn-trigger-bulk-verify-water');

        $respLvsb = $this->actingAs($teknisi)->get(route('public-area.lvsb.index'));
        $respLvsb->assertStatus(200);
        $respLvsb->assertDontSee('bulk-master-lvsb');

        $respMvsb = $this->actingAs($teknisi)->get(route('public-area.mvsb.index'));
        $respMvsb->assertStatus(200);
        $respMvsb->assertDontSee('bulk-master-mvsb');
    }

    public function test_superadmin_can_bulk_verify_water_meter_readings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.0,
            'user_id'     => $teknisi->id,
        ]);

        $r2 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-02',
            'category'    => 'water',
            'meter_key'   => 'pantry',
            'meter_name'  => 'Pantry Office',
            'unit'        => 'm³',
            'stand_meter' => 200.0,
            'user_id'     => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.water.bulk.verify'), [
            'ids' => [$r1->id, $r2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $r1->refresh();
        $r2->refresh();

        $this->assertNotNull($r1->verified_at);
        $this->assertEquals($admin->id, $r1->verified_by);
        $this->assertNotNull($r2->verified_at);
        $this->assertEquals($admin->id, $r2->verified_by);
    }

    public function test_superadmin_can_bulk_delete_water_meter_readings_and_photos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $photoFile = UploadedFile::fake()->create('water1.jpg', 100, 'image/jpeg')->store('public_area/water', 'public');
        Storage::disk('public')->assertExists($photoFile);

        $r1 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.0,
            'foto'        => $photoFile,
            'user_id'     => $teknisi->id,
        ]);

        $r2 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-02',
            'category'    => 'water',
            'meter_key'   => 'pantry',
            'meter_name'  => 'Pantry Office',
            'unit'        => 'm³',
            'stand_meter' => 200.0,
            'user_id'     => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.water.bulk.destroy'), [
            'ids' => [$r1->id, $r2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('public_area_meter_readings', ['id' => $r1->id]);
        $this->assertDatabaseMissing('public_area_meter_readings', ['id' => $r2->id]);
        Storage::disk('public')->assertMissing($photoFile);
    }

    public function test_superadmin_can_bulk_verify_lvsb_readings(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = PublicAreaLvsbReading::create([
            'tanggal'  => '2026-10-01',
            'user_id'  => $teknisi->id,
            'readings' => ['incoming_trafo' => ['voltage_3p' => 380]],
        ]);

        $r2 = PublicAreaLvsbReading::create([
            'tanggal'  => '2026-10-02',
            'user_id'  => $teknisi->id,
            'readings' => ['incoming_trafo' => ['voltage_3p' => 382]],
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.lvsb.bulk.verify'), [
            'ids' => [$r1->id, $r2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $r1->refresh();
        $r2->refresh();

        $this->assertNotNull($r1->verified_at);
        $this->assertEquals($admin->id, $r1->verified_by);
        $this->assertNotNull($r2->verified_at);
        $this->assertEquals($admin->id, $r2->verified_by);
    }

    public function test_superadmin_can_bulk_delete_lvsb_readings_and_photos_including_fotos_array(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $photoSingle = UploadedFile::fake()->create('lvsb_single.jpg', 100, 'image/jpeg')->store('public_area/lvsb', 'public');
        $photoSub1 = UploadedFile::fake()->create('lvsb_sub1.jpg', 100, 'image/jpeg')->store('public_area/lvsb', 'public');
        $photoSub2 = UploadedFile::fake()->create('lvsb_sub2.jpg', 100, 'image/jpeg')->store('public_area/lvsb', 'public');

        Storage::disk('public')->assertExists($photoSingle);
        Storage::disk('public')->assertExists($photoSub1);
        Storage::disk('public')->assertExists($photoSub2);

        $r1 = PublicAreaLvsbReading::create([
            'tanggal'  => '2026-10-01',
            'user_id'  => $teknisi->id,
            'foto'     => $photoSingle,
            'fotos'    => ['incoming_trafo' => $photoSub1, 'riser_1' => $photoSub2],
            'readings' => ['incoming_trafo' => ['voltage_3p' => 380]],
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.lvsb.bulk.destroy'), [
            'ids' => [$r1->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('public_area_lvsb_readings', ['id' => $r1->id]);
        Storage::disk('public')->assertMissing($photoSingle);
        Storage::disk('public')->assertMissing($photoSub1);
        Storage::disk('public')->assertMissing($photoSub2);
    }

    public function test_superadmin_can_bulk_verify_mvsb_readings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = PublicAreaMvsbReading::create([
            'tanggal'      => '2026-10-01',
            'voltage_20kv' => 20100,
            'current'      => 32,
            'user_id'      => $teknisi->id,
        ]);

        $r2 = PublicAreaMvsbReading::create([
            'tanggal'      => '2026-10-02',
            'voltage_20kv' => 20150,
            'current'      => 35,
            'user_id'      => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.mvsb.bulk.verify'), [
            'ids' => [$r1->id, $r2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $r1->refresh();
        $r2->refresh();

        $this->assertNotNull($r1->verified_at);
        $this->assertEquals($admin->id, $r1->verified_by);
        $this->assertNotNull($r2->verified_at);
        $this->assertEquals($admin->id, $r2->verified_by);
    }

    public function test_superadmin_can_bulk_delete_mvsb_readings_and_photos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $photoMvsb = UploadedFile::fake()->create('mvsb.jpg', 100, 'image/jpeg')->store('public_area/mvsb', 'public');
        Storage::disk('public')->assertExists($photoMvsb);

        $r1 = PublicAreaMvsbReading::create([
            'tanggal'      => '2026-10-01',
            'voltage_20kv' => 20100,
            'current'      => 32,
            'foto'         => $photoMvsb,
            'user_id'      => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('public-area.mvsb.bulk.destroy'), [
            'ids' => [$r1->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('public_area_mvsb_readings', ['id' => $r1->id]);
        Storage::disk('public')->assertMissing($photoMvsb);
    }

    public function test_bulk_action_fails_if_date_range_exceeds_one_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-01-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.0,
            'user_id'     => $teknisi->id,
        ]);

        $r2 = PublicAreaMeterReading::create([
            'tanggal'     => '2026-03-01', // > 31 hari
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 150.0,
            'user_id'     => $teknisi->id,
        ]);

        $respVerify = $this->actingAs($admin)->post(route('public-area.water.bulk.verify'), [
            'ids' => [$r1->id, $r2->id],
        ]);
        $respVerify->assertSessionHas('error');
        $r1->refresh();
        $this->assertNull($r1->verified_at);

        $respDestroy = $this->actingAs($admin)->post(route('public-area.water.bulk.destroy'), [
            'ids' => [$r1->id, $r2->id],
        ]);
        $respDestroy->assertSessionHas('error');
        $this->assertDatabaseHas('public_area_meter_readings', ['id' => $r1->id]);
    }

    public function test_non_superadmin_cannot_bulk_verify_or_bulk_delete(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r = PublicAreaMeterReading::create([
            'tanggal'     => '2026-10-01',
            'category'    => 'water',
            'meter_key'   => 'cooling_tower',
            'meter_name'  => 'Cooling Tower 12A',
            'unit'        => 'm³',
            'stand_meter' => 100.0,
            'user_id'     => $teknisi->id,
        ]);

        $this->actingAs($teknisi)->post(route('public-area.water.bulk.verify'), ['ids' => [$r->id]])->assertStatus(403);
        $this->actingAs($teknisi)->post(route('public-area.water.bulk.destroy'), ['ids' => [$r->id]])->assertStatus(403);

        $this->actingAs($teknisi)->post(route('public-area.lvsb.bulk.verify'), ['ids' => [1]])->assertStatus(403);
        $this->actingAs($teknisi)->post(route('public-area.lvsb.bulk.destroy'), ['ids' => [1]])->assertStatus(403);

        $this->actingAs($teknisi)->post(route('public-area.mvsb.bulk.verify'), ['ids' => [1]])->assertStatus(403);
        $this->actingAs($teknisi)->post(route('public-area.mvsb.bulk.destroy'), ['ids' => [1]])->assertStatus(403);
    }

    public function test_bulk_action_with_empty_ids_redirects_with_warning(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $resp = $this->actingAs($admin)->post(route('public-area.water.bulk.verify'), ['ids' => []]);
        $resp->assertSessionHas('warning');

        $resp = $this->actingAs($admin)->post(route('public-area.lvsb.bulk.destroy'), ['ids' => []]);
        $resp->assertSessionHas('warning');

        $resp = $this->actingAs($admin)->post(route('public-area.mvsb.bulk.verify'), ['ids' => []]);
        $resp->assertSessionHas('warning');
    }
}
