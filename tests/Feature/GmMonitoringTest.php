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

    public function test_gm_sees_actual_pln_kwh_multiplied_by_factor_meter_5000_and_rupiah_calculation_with_tax(): void
    {
        $gm = User::factory()->create(['role' => 'gm']);

        $d1 = now()->startOfMonth()->format('Y-m-d');
        $d2 = now()->startOfMonth()->addDay()->format('Y-m-d');

        MeterReading::create([
            'user_id'     => $gm->id,
            'tanggal'     => $d1,
            'waktu'       => '08:00:00',
            'stand_bp'    => 10.00,
            'stand_lbp1'  => 20.00,
            'stand_lbp2'  => 30.00,
            'stand_total' => 60.00,
            'stand_wbp'   => 10.00,
            'stand_lwbp'  => 50.00,
        ]);

        MeterReading::create([
            'user_id'     => $gm->id,
            'tanggal'     => $d2,
            'waktu'       => '08:00:00',
            'stand_bp'    => 12.00,  // delta BP = 2.00 -> 2 x 5000 = 10.000 kWh WBP
            'stand_lbp1'  => 23.00,  // delta LBP1 = 3.00
            'stand_lbp2'  => 34.00,  // delta LBP2 = 4.00 -> (3x5000) + (4x5000) = 35.000 kWh LWBP
            'stand_total' => 69.00,
            'stand_wbp'   => 12.00,
            'stand_lwbp'  => 57.00,
        ]);

        $response = $this->actingAs($gm)->get(route('gm.monitoring', ['periode' => now()->format('Y-m')]));

        $response->assertStatus(200);

        // Pastikan kalkulasi actual kWh dan Rupiah tampil
        // LWBP: 35.000 kWh, WBP: 10.000 kWh, Total: 45.000 kWh
        $response->assertSee('45.000,00');
        $response->assertSee('35.000,00');
        $response->assertSee('10.000,00');

        // Subtotal: 35.000 * 1035.78 = 36.252.300 + 10.000 * 1553.67 = 15.536.700 => 51.789.000
        // PPJ 2.4%: 1.242.936
        // Total: 53.031.936
        $response->assertSee('51.789.000,00');
        $response->assertSee('1.242.936,00');
        $response->assertSee('53.031.936,00');

        // Pastikan badge & label strategis tampil
        $response->assertSee('Faktor Meter:');
        $response->assertSee('5.000x');
        $response->assertSee('Tarif LWBP:');
        $response->assertSee('1.035,78');
        $response->assertSee('Tarif WBP:');
        $response->assertSee('1.553,67');
        $response->assertSee('Pajak Penerangan:');
        $response->assertSee('2,4%');
    }
}
