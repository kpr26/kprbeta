<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkActionLogsheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_sees_bulk_actions_toolbar_and_checkboxes_on_index_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        MeterReading::create([
            'tanggal'   => '2026-10-01',
            'stand_bp'  => 1000.0,
            'stand_air' => 500.0,
            'user_id'   => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->get(route('logsheet.index', ['periode' => '2026-10']));
        $response->assertStatus(200);
        $response->assertSee('Pilih Semua Bulan Ini');
        $response->assertSee('Verifikasi Masal');
        $response->assertSee('Hapus Masal');
        $response->assertSee('Pilih Belum Verifikasi');
        $response->assertSee('bulk-item-check');
    }

    public function test_teknisi_and_regular_user_do_not_see_bulk_actions_toolbar(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $user = User::factory()->create(['role' => 'user']);

        MeterReading::create([
            'tanggal'   => '2026-10-01',
            'stand_bp'  => 1000.0,
            'stand_air' => 500.0,
            'user_id'   => $teknisi->id,
        ]);

        $respTeknisi = $this->actingAs($teknisi)->get(route('logsheet.index', ['periode' => '2026-10']));
        $respTeknisi->assertStatus(200);
        $respTeknisi->assertDontSee('Pilih Semua Bulan Ini');
        $respTeknisi->assertDontSee('btn-trigger-bulk-verify');
        $respTeknisi->assertDontSee('btn-trigger-bulk-delete');

        $respUser = $this->actingAs($user)->get(route('logsheet.index', ['periode' => '2026-10']));
        $respUser->assertStatus(200);
        $respUser->assertDontSee('Pilih Semua Bulan Ini');
    }

    public function test_superadmin_can_bulk_verify_selected_logsheets_within_one_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = MeterReading::create([
            'tanggal'   => '2026-05-01',
            'stand_bp'  => 1000.0,
            'stand_air' => 500.0,
            'user_id'   => $teknisi->id,
        ]);

        $r2 = MeterReading::create([
            'tanggal'   => '2026-05-02',
            'stand_bp'  => 1010.0,
            'stand_air' => 510.0,
            'user_id'   => $teknisi->id,
        ]);

        $r3 = MeterReading::create([
            'tanggal'   => '2026-05-03',
            'stand_bp'  => 1020.0,
            'stand_air' => 520.0,
            'user_id'   => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('logsheet.bulk.verify'), [
            'ids'     => [$r1->id, $r2->id, $r3->id],
            'periode' => '2026-05',
        ]);

        $response->assertRedirect(route('logsheet.index', ['periode' => '2026-05']));
        $response->assertSessionHas('success');

        $r1->refresh();
        $r2->refresh();
        $r3->refresh();

        $this->assertNotNull($r1->verified_at);
        $this->assertEquals($admin->id, $r1->verified_by);
        $this->assertNotNull($r2->verified_at);
        $this->assertEquals($admin->id, $r2->verified_by);
        $this->assertNotNull($r3->verified_at);
        $this->assertEquals($admin->id, $r3->verified_by);
    }

    public function test_superadmin_can_bulk_delete_selected_logsheets_and_their_photos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $photoKwh = UploadedFile::fake()->create('kwh.jpg', 100, 'image/jpeg')->store('logsheet', 'public');
        $photoAir = UploadedFile::fake()->create('air.jpg', 100, 'image/jpeg')->store('logsheet', 'public');

        Storage::disk('public')->assertExists($photoKwh);
        Storage::disk('public')->assertExists($photoAir);

        $r1 = MeterReading::create([
            'tanggal'   => '2026-05-01',
            'stand_bp'  => 1000.0,
            'stand_air' => 500.0,
            'foto_kwh'  => $photoKwh,
            'foto_air'  => $photoAir,
            'user_id'   => $teknisi->id,
        ]);

        $r2 = MeterReading::create([
            'tanggal'   => '2026-05-02',
            'stand_bp'  => 1010.0,
            'stand_air' => 510.0,
            'user_id'   => $teknisi->id,
        ]);

        $response = $this->actingAs($admin)->post(route('logsheet.bulk.destroy'), [
            'ids'     => [$r1->id, $r2->id],
            'periode' => '2026-05',
        ]);

        $response->assertRedirect(route('logsheet.index', ['periode' => '2026-05']));
        $response->assertSessionHas('success');

        // Records should be gone
        $this->assertDatabaseMissing('meter_readings', ['id' => $r1->id]);
        $this->assertDatabaseMissing('meter_readings', ['id' => $r2->id]);

        // Photos should be deleted from storage
        Storage::disk('public')->assertMissing($photoKwh);
        Storage::disk('public')->assertMissing($photoAir);
    }

    public function test_bulk_action_fails_if_date_range_exceeds_one_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $r1 = MeterReading::create([
            'tanggal'  => '2026-01-01',
            'stand_bp' => 1000.0,
            'user_id'  => $teknisi->id,
        ]);

        $r2 = MeterReading::create([
            'tanggal'  => '2026-03-15', // Lebih dari 31 hari
            'stand_bp' => 1200.0,
            'user_id'  => $teknisi->id,
        ]);

        // Bulk verify test
        $respVerify = $this->actingAs($admin)->post(route('logsheet.bulk.verify'), [
            'ids' => [$r1->id, $r2->id],
        ]);
        $respVerify->assertSessionHas('error');
        $r1->refresh();
        $this->assertNull($r1->verified_at);

        // Bulk destroy test
        $respDestroy = $this->actingAs($admin)->post(route('logsheet.bulk.destroy'), [
            'ids' => [$r1->id, $r2->id],
        ]);
        $respDestroy->assertSessionHas('error');
        $this->assertDatabaseHas('meter_readings', ['id' => $r1->id]);
        $this->assertDatabaseHas('meter_readings', ['id' => $r2->id]);
    }

    public function test_non_superadmin_cannot_bulk_verify_or_bulk_delete(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $gm = User::factory()->create(['role' => 'gm']);

        $r = MeterReading::create([
            'tanggal'  => '2026-05-01',
            'stand_bp' => 1000.0,
            'user_id'  => $teknisi->id,
        ]);

        $this->actingAs($teknisi)->post(route('logsheet.bulk.verify'), ['ids' => [$r->id]])->assertStatus(403);
        $this->actingAs($teknisi)->post(route('logsheet.bulk.destroy'), ['ids' => [$r->id]])->assertStatus(403);

        $this->actingAs($gm)->post(route('logsheet.bulk.verify'), ['ids' => [$r->id]])->assertStatus(403);
        $this->actingAs($gm)->post(route('logsheet.bulk.destroy'), ['ids' => [$r->id]])->assertStatus(403);
    }

    public function test_bulk_action_with_empty_ids_redirects_with_warning(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $respVerify = $this->actingAs($admin)->post(route('logsheet.bulk.verify'), ['ids' => []]);
        $respVerify->assertSessionHas('warning');

        $respDestroy = $this->actingAs($admin)->post(route('logsheet.bulk.destroy'), ['ids' => []]);
        $respDestroy->assertSessionHas('warning');
    }
}
