<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\PublicAreaLvsbReading;
use App\Models\PublicAreaMeterReading;
use App\Models\PublicAreaMvsbReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GmMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_gm_monitoring(): void
    {
        $response = $this->get(route('gm.monitoring'));
        $response->assertRedirect(route('login'));
    }

    public function test_teknisi_cannot_access_gm_monitoring(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($teknisi)->get(route('gm.monitoring'));
        $response->assertStatus(403);
    }

    public function test_user_cannot_access_gm_monitoring(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('gm.monitoring'));
        $response->assertStatus(403);
    }

    public function test_gm_can_access_gm_monitoring_and_sees_executive_kpi(): void
    {
        $gm = User::factory()->create([
            'role' => 'gm',
            'name' => 'Bapak Hendra GM',
        ]);

        $this->assertTrue($gm->isGm());

        // Create sample data for today
        MeterReading::create([
            'user_id'     => $gm->id,
            'tanggal'     => today(),
            'waktu'       => '08:00:00',
            'stand_bp'    => 100,
            'stand_lbp1'  => 200,
            'stand_lbp2'  => 300,
            'stand_total' => 600,
            'stand_awal'  => 1000,
            'stand_akhir' => 1025,
            'pemakaian'   => 25,
        ]);

        $response = $this->actingAs($gm)->get(route('gm.monitoring'));

        $response->assertStatus(200);
        $response->assertSee('Executive Monitoring');
        $response->assertSee('Executive Summary');
        $response->assertSee('Bapak Hendra GM');
        $response->assertSee('Status Pencatatan Hari Ini');
    }

    public function test_superadmin_can_access_gm_monitoring(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('gm.monitoring'));
        $response->assertStatus(200);
    }

    public function test_gm_can_export_logsheet_and_public_area_reports(): void
    {
        $gm = User::factory()->create(['role' => 'gm']);

        // Logsheet Gedung Excel & PDF
        $responseExcel = $this->actingAs($gm)->get(route('logsheet.excel', ['period' => now()->format('Y-m')]));
        $responseExcel->assertSuccessful();

        $responsePdf = $this->actingAs($gm)->get(route('logsheet.pdf', ['period' => now()->format('Y-m')]));
        $responsePdf->assertSuccessful();

        // Public area Water Excel & PDF
        $responseWaterExcel = $this->actingAs($gm)->get(route('public-area.excel', ['category' => 'water']));
        $responseWaterExcel->assertSuccessful();

        // LVSB & MVSB export
        $responseLvsbExcel = $this->actingAs($gm)->get(route('public-area.lvsb.excel'));
        $responseLvsbExcel->assertSuccessful();

        $responseMvsbExcel = $this->actingAs($gm)->get(route('public-area.mvsb.excel'));
        $responseMvsbExcel->assertSuccessful();
    }

    public function test_gm_cannot_verify_or_delete_logsheets(): void
    {
        $gm = User::factory()->create(['role' => 'gm']);
        $logsheet = MeterReading::create([
            'user_id'     => $gm->id,
            'tanggal'     => today(),
            'waktu'       => '08:00:00',
            'stand_awal'  => 100,
            'stand_akhir' => 110,
            'pemakaian'   => 10,
        ]);

        // Attempt verify -> 403
        $verifyResponse = $this->actingAs($gm)->post(route('logsheet.verify', $logsheet));
        $verifyResponse->assertStatus(403);

        // Attempt delete -> 403
        $deleteResponse = $this->actingAs($gm)->delete(route('logsheet.destroy', $logsheet));
        $deleteResponse->assertStatus(403);
    }

    public function test_admin_can_create_and_update_user_with_role_gm(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create new user with role 'gm'
        $createResponse = $this->actingAs($admin)->post(route('profile.storeUser'), [
            'new_name'                  => 'Pak General Manager Baru',
            'new_username'              => 'gm_baru',
            'new_email'                 => 'gmbaru@kprbeta.com',
            'new_role'                  => 'gm',
            'new_password'              => 'password123',
            'new_password_confirmation' => 'password123',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'username' => 'gm_baru',
            'role'     => 'gm',
        ]);

        $createdUser = User::where('username', 'gm_baru')->first();

        // Update role
        $updateResponse = $this->actingAs($admin)->put(route('profile.updateUser', $createdUser), [
            'edit_name'      => 'Pak GM Updated',
            'edit_username'  => 'gm_baru',
            'edit_email'     => 'gmbaru@kprbeta.com',
            'edit_role'      => 'gm',
            'edit_is_active' => 1,
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'username' => 'gm_baru',
            'name'     => 'Pak GM Updated',
            'role'     => 'gm',
        ]);
    }
}
