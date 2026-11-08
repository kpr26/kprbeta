<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantEnergyReading;
use App\Models\TenantWaterReading;
use App\Models\BillingPeriodSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillingSystemTest extends TestCase
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

    public function test_all_roles_can_access_billing_utilities_hub(): void
    {
        $roles = ['admin', 'superadmin', 'eng_admin', 'eng_spv', 'teknisi', 'gm', 'user'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)->get(route('billing.utilities.hub'));
            $response->assertOk();
            $response->assertSee('Billing System');
            $response->assertSee('Tenant Energy Usage');
            $response->assertSee('Tenant Water Usage');
        }
    }

    public function test_can_create_and_list_tenant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('billing.tenants.store'), [
            'nama_tenant'         => 'PT Cahaya Abadi',
            'kode_tenant'         => 'TNT-202',
            'lantai'              => 'Lantai 2',
            'nomor_unit'          => 'Unit 202',
            'nomor_meter_listrik' => 'KWH-202',
            'nomor_meter_air'     => 'PAM-202',
            'faktor_kali_listrik' => 1.0,
            'faktor_kali_air'     => 1.0,
            'daya_listrik_va'     => 22000,
            'is_active'           => 1,
        ]);

        $response->assertRedirect(route('billing.tenants.index'));

        $this->assertDatabaseHas('tenants', [
            'nama_tenant' => 'PT Cahaya Abadi',
            'kode_tenant' => 'TNT-202',
        ]);
    }

    public function test_teknisi_can_input_energy_usage_with_photo_and_auto_calculation(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $tenant = Tenant::create([
            'nama_tenant'         => 'Starbucks Coffee',
            'kode_tenant'         => 'TNT-G02',
            'lantai'              => 'Ground',
            'nomor_unit'          => 'G-02',
            'nomor_meter_listrik' => 'KWH-SBX-02',
            'faktor_kali_listrik' => 1.0,
            'is_active'           => true,
        ]);

        $photo = $this->createFakePng('meter_kwh.png');

        $response = $this->actingAs($teknisi)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 1000.00,
            'stand_akhir'   => 1250.50,
            'foto_meter'    => $photo,
            'catatan'       => 'Pencatatan normal bulanan',
        ]);

        $response->assertRedirect(route('billing.energy.index', ['year' => 2026, 'month' => 11]));

        $reading = TenantEnergyReading::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($reading);
        $this->assertEquals(1000.00, $reading->stand_awal);
        $this->assertEquals(1250.50, $reading->stand_akhir);
        $this->assertEquals(250.50, $reading->pemakaian); // 1250.50 - 1000.00
        $this->assertNotNull($reading->foto_meter);
        Storage::disk('public')->assertExists($reading->foto_meter);
    }

    public function test_teknisi_can_input_water_usage_with_photo_and_auto_calculation(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $tenant = Tenant::create([
            'nama_tenant'     => 'Starbucks Coffee',
            'kode_tenant'     => 'TNT-G02',
            'lantai'          => 'Ground',
            'nomor_unit'      => 'G-02',
            'nomor_meter_air' => 'PAM-SBX-02',
            'faktor_kali_air' => 1.0,
            'is_active'       => true,
        ]);

        $photo = $this->createFakePng('meter_water.png');

        $response = $this->actingAs($teknisi)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 200.00,
            'stand_akhir'   => 245.50,
            'foto_meter'    => $photo,
            'catatan'       => 'Pencatatan water meter',
        ]);

        $response->assertRedirect(route('billing.water.index', ['year' => 2026, 'month' => 11]));

        $reading = TenantWaterReading::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($reading);
        $this->assertEquals(200.00, $reading->stand_awal);
        $this->assertEquals(245.50, $reading->stand_akhir);
        $this->assertEquals(45.50, $reading->pemakaian);
        $this->assertNotNull($reading->foto_meter);
        Storage::disk('public')->assertExists($reading->foto_meter);
    }

    public function test_quick_input_stores_both_energy_and_water_seamlessly(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $tenant = Tenant::create([
            'nama_tenant'         => 'Bank Mandiri KCP',
            'kode_tenant'         => 'TNT-G01',
            'lantai'              => 'Ground',
            'nomor_unit'          => 'G-01',
            'nomor_meter_listrik' => 'KWH-MDR-01',
            'nomor_meter_air'     => 'PAM-MDR-01',
            'is_active'           => true,
        ]);

        $kwhPhoto   = $this->createFakePng('kwh.png');
        $waterPhoto = $this->createFakePng('water.png');

        $response = $this->actingAs($teknisi)->post(route('billing.quick-input.store'), [
            'tenant_id'           => $tenant->id,
            'periode_tahun'       => 2026,
            'periode_bulan'       => 11,
            'tanggal_catat'       => '2026-11-08',
            'stand_awal_listrik'  => 5000.00,
            'stand_akhir_listrik' => 5800.00,
            'foto_meter_listrik'  => $kwhPhoto,
            'stand_awal_air'      => 300.00,
            'stand_akhir_air'     => 340.00,
            'foto_meter_air'      => $waterPhoto,
            'catatan'             => 'Input cepat rute bulanan',
        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('tenant_energy_readings', [
            'tenant_id'   => $tenant->id,
            'stand_akhir' => 5800.00,
            'pemakaian'   => 800.00,
        ]);

        $this->assertDatabaseHas('tenant_water_readings', [
            'tenant_id'   => $tenant->id,
            'stand_akhir' => 340.00,
            'pemakaian'   => 40.00,
        ]);
    }

    public function test_export_excel_and_pdf_energy_and_water(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Excel Energy
        $response = $this->actingAs($user)->get(route('billing.energy.excel', ['year' => 2026, 'month' => 11]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');

        // PDF Energy
        $response = $this->actingAs($user)->get(route('billing.energy.pdf', ['year' => 2026, 'month' => 11]));
        $response->assertOk();

        // Excel Water
        $response = $this->actingAs($user)->get(route('billing.water.excel', ['year' => 2026, 'month' => 11]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');

        // PDF Water
        $response = $this->actingAs($user)->get(route('billing.water.pdf', ['year' => 2026, 'month' => 11]));
        $response->assertOk();
    }

    public function test_super_admin_can_toggle_session_and_non_admin_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        // Teknisi cannot toggle session (403)
        $response = $this->actingAs($teknisi)->post(route('billing.utilities.toggle-session'), [
            'action' => 'close',
        ]);
        $response->assertForbidden();

        // Super Admin can close session
        $response = $this->actingAs($admin)->post(route('billing.utilities.toggle-session'), [
            'action' => 'close',
            'keterangan' => 'Ditutup sementara',
        ]);
        $response->assertSessionHas('warning');
        $this->assertFalse(BillingPeriodSetting::isOpen());

        // Super Admin can reopen session
        $response = $this->actingAs($admin)->post(route('billing.utilities.toggle-session'), [
            'action'       => 'open',
            'active_year'  => 2026,
            'active_month' => 11,
            'keterangan'   => 'Dibuka kembali untuk input November',
        ]);
        $response->assertSessionHas('success');
        $this->assertTrue(BillingPeriodSetting::isOpen());
        $this->assertEquals(2026, BillingPeriodSetting::current()->active_year);
        $this->assertEquals(11, BillingPeriodSetting::current()->active_month);
    }

    public function test_when_session_is_closed_teknisi_is_blocked_from_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        // Close session
        BillingPeriodSetting::closeSession($admin->id, 'Periode November selesai');
        $this->assertFalse(BillingPeriodSetting::isOpen());

        // 1. Quick input shows locked view
        $response = $this->actingAs($teknisi)->get(route('billing.quick-input'));
        $response->assertOk();
        $response->assertSee('Sesi Penginputan Utilitas Ditutup');

        // 2. Energy create shows locked view
        $response = $this->actingAs($teknisi)->get(route('billing.energy.create'));
        $response->assertOk();
        $response->assertSee('Sesi Penginputan Utilitas Ditutup');

        // 3. Water create shows locked view
        $response = $this->actingAs($teknisi)->get(route('billing.water.create'));
        $response->assertOk();
        $response->assertSee('Sesi Penginputan Utilitas Ditutup');

        $tenant = Tenant::create([
            'nama_tenant'         => 'Test Tenant Locked',
            'kode_tenant'         => 'TNT-LCK',
            'lantai'              => 'Lantai 1',
            'nomor_unit'          => 'Unit 101',
            'nomor_meter_listrik' => 'KWH-LCK',
            'nomor_meter_air'     => 'PAM-LCK',
            'is_active'           => true,
        ]);

        // 4. POST Energy store is blocked
        $response = $this->actingAs($teknisi)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 100,
            'stand_akhir'   => 150,
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_energy_readings', ['tenant_id' => $tenant->id]);

        // 5. POST Water store is blocked
        $response = $this->actingAs($teknisi)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 10,
            'stand_akhir'   => 20,
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_water_readings', ['tenant_id' => $tenant->id]);

        // 6. POST Quick-input AJAX is blocked with 403
        $response = $this->actingAs($teknisi)->post(route('billing.quick-input.store'), [
            'tenant_id'           => $tenant->id,
            'periode_tahun'       => 2026,
            'periode_bulan'       => 11,
            'tanggal_catat'       => '2026-11-08',
            'stand_awal_listrik'  => 100,
            'stand_akhir_listrik' => 150,
        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(403);
    }

    public function test_super_admin_can_bypass_session_lock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        BillingPeriodSetting::closeSession($admin->id, 'Terkunci untuk staff');

        // Admin can still access forms
        $response = $this->actingAs($admin)->get(route('billing.quick-input'));
        $response->assertOk();
        $response->assertDontSee('Sesi Penginputan Utilitas Ditutup');
        $response->assertSee('Checklist Rute Pencatatan Meteran Bulanan');

        $response = $this->actingAs($admin)->get(route('billing.energy.create'));
        $response->assertOk();
        $response->assertDontSee('Sesi Penginputan Utilitas Ditutup');

        $response = $this->actingAs($admin)->get(route('billing.water.create'));
        $response->assertOk();
        $response->assertDontSee('Sesi Penginputan Utilitas Ditutup');
    }

    public function test_super_admin_can_bulk_import_electricity_csv_with_ev_chargers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $csvContent = implode("\n", [
            ",,RESIDENCES METER READING ELECTRICITY & WATER,,,,,,,,,,,,,,,,,,,,,,,",
            ",,BULAN   : OCTOBER 2026,,,,,,,,,,,,,,,,Daftar EV Charging Tenant,,,,,,,",
            ",No,Floor,Unit,LISTRIK,,,,\"TARIF (1699,53)\",,,,,,,Keterangan,,,No.,Unit,,,,,,",
            ",,,,Daya,Awal,Akhir,Pemakaian,Min Hour (40 Jam),Min Charge (40 Jam),Actual Charge,Billing,\"PPJ (2,40%)\",Biaya Materai,Total Tagihan,,,,,,,,,,,",
            ",19,17,DE,33,250875.7,255316.6,4440.9,1320,2243379.6,7547442.78,7547442.78,181138.63,10000,7738581.4,,,,,,,,,,,",
            ",20,17/ CAR ,DE,33,2628.43,2851.1,222.67,1320,2243379.6,378434.35,2243379.6,53841.11,10000,2307220.71,,,,,,,,,,,",
        ]);

        $response = $this->actingAs($admin)->post(route('billing.energy.bulk.store'), [
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'raw_csv'       => $csvContent,
        ]);

        $response->assertRedirect(route('billing.energy.index', ['year' => 2026, 'month' => 10]));
        $response->assertSessionHas('success');

        // Harus ada 1 tenant dengan lantai 17 unit DE
        $tenant = Tenant::where('lantai', '17')->where('nomor_unit', 'DE')->first();
        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->has_ev_charger);
        $this->assertEquals(33.00, $tenant->daya_listrik);
        $this->assertEquals(33.00, $tenant->daya_ev);

        // Dan 2 reading untuk tenant tersebut di Oktober 2026: regular dan ev
        $regReading = TenantEnergyReading::where('tenant_id', $tenant->id)->where('meter_type', 'regular')->first();
        $this->assertNotNull($regReading);
        $this->assertEquals(250875.7, (float)$regReading->stand_awal);
        $this->assertEquals(255316.6, (float)$regReading->stand_akhir);
        $this->assertEquals(4440.9, (float)$regReading->pemakaian);

        $evReading = TenantEnergyReading::where('tenant_id', $tenant->id)->where('meter_type', 'ev')->first();
        $this->assertNotNull($evReading);
        $this->assertEquals(2628.43, (float)$evReading->stand_awal);
        $this->assertEquals(2851.1, (float)$evReading->stand_akhir);
        $this->assertEquals(222.67, (float)$evReading->pemakaian);
    }

    public function test_non_super_admin_cannot_access_bulk_electricity_import(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $spv = User::factory()->create(['role' => 'eng_spv']);

        $response = $this->actingAs($teknisi)->get(route('billing.energy.bulk.create'));
        $response->assertForbidden();

        $response = $this->actingAs($spv)->get(route('billing.energy.bulk.create'));
        $response->assertForbidden();

        $response = $this->actingAs($teknisi)->post(route('billing.energy.bulk.store'), [
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'raw_csv'       => '1,12,A1,21.12,100,200',
        ]);
        $response->assertForbidden();
    }

    public function test_quick_input_supports_ev_charger_input_for_same_unit(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $tenant = Tenant::create([
            'nama_tenant'         => 'Unit 22 A1',
            'kode_tenant'         => 'TNT-22-A1',
            'lantai'              => '22',
            'nomor_unit'          => 'A1',
            'daya_listrik'        => 21.12,
            'has_ev_charger'      => true,
            'daya_ev'             => 21.12,
            'nomor_meter_listrik' => 'KWH-22-A1',
            'nomor_meter_ev'      => 'KWH-22-A1-EV',
            'is_active'           => true,
        ]);

        $response = $this->actingAs($teknisi)->post(route('billing.quick-input.store'), [
            'tenant_id'           => $tenant->id,
            'periode_tahun'       => 2026,
            'periode_bulan'       => 11,
            'tanggal_catat'       => '2026-11-08',
            'stand_awal_listrik'  => 180206.4,
            'stand_akhir_listrik' => 180879.2,
            'foto_meter_listrik'  => $this->createFakePng('meter_reguler.png'),
            'stand_awal_ev'       => 367.37,
            'stand_akhir_ev'      => 367.93,
            'foto_meter_ev'       => $this->createFakePng('meter_ev.png'),
        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);

        // Verifikasi kedua reading tersimpan untuk tenant id yang sama
        $this->assertDatabaseHas('tenant_energy_readings', [
            'tenant_id'   => $tenant->id,
            'meter_type'  => 'regular',
            'stand_akhir' => 180879.2,
            'pemakaian'   => 672.8,
        ]);

        $this->assertDatabaseHas('tenant_energy_readings', [
            'tenant_id'   => $tenant->id,
            'meter_type'  => 'ev',
            'stand_akhir' => 367.93,
            'pemakaian'   => 0.56,
        ]);
    }

    public function test_export_csv_and_excel_match_october_format(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $tenant = Tenant::create([
            'nama_tenant'    => 'Unit 17 DE',
            'kode_tenant'    => 'TNT-17-DE',
            'lantai'         => '17',
            'nomor_unit'     => 'DE',
            'daya_listrik'   => 33.00,
            'has_ev_charger' => true,
            'daya_ev'        => 33.00,
            'is_active'      => true,
        ]);

        TenantEnergyReading::create([
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'daya'          => 33.00,
            'stand_awal'    => 250875.7,
            'stand_akhir'   => 255316.6,
            'pemakaian'     => 4440.9,
            'tarif_per_kwh' => 1699.53,
        ]);

        TenantEnergyReading::create([
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'ev',
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'daya'          => 33.00,
            'stand_awal'    => 2628.43,
            'stand_akhir'   => 2851.1,
            'pemakaian'     => 222.67,
            'tarif_per_kwh' => 1699.53,
        ]);

        // 1. Export Excel
        $response = $this->actingAs($admin)->get(route('billing.energy.excel', ['year' => 2026, 'month' => 10]));
        $response->assertOk();
        $response->assertSee('RESIDENCES METER READING ELECTRICITY');
        $response->assertSee('Daftar EV Charging Tenant');
        $response->assertSee('17/ CAR');
        $response->assertSee('T O T A L');

        // 2. Export CSV
        $response = $this->actingAs($admin)->get(route('billing.energy.csv', ['year' => 2026, 'month' => 10]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('17/ CAR');
        $response->assertSee('Daftar EV Charging Tenant');
    }

    public function test_teknisi_cannot_input_stand_mundur_in_water_and_energy(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $tenant = Tenant::create([
            'nama_tenant'         => 'Unit 19 D',
            'kode_tenant'         => 'TNT-19-D',
            'lantai'              => '19',
            'nomor_unit'          => 'D',
            'nomor_meter_listrik' => 'KWH-19-D',
            'nomor_meter_air'     => 'PAM-19-D',
            'is_active'           => true,
        ]);

        // 1. Water: stand akhir < stand awal rejected for teknisi
        $response = $this->actingAs($teknisi)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 500.000,
            'stand_akhir'   => 450.000, // Mundur!
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_water_readings', ['tenant_id' => $tenant->id]);

        // 2. Energy: stand akhir < stand awal rejected for teknisi
        $response = $this->actingAs($teknisi)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 5000.00,
            'stand_akhir'   => 4500.00, // Mundur!
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_energy_readings', ['tenant_id' => $tenant->id]);

        // 3. Quick Input: stand akhir < stand awal rejected for teknisi
        $response = $this->actingAs($teknisi)->post(route('billing.quick-input.store'), [
            'tenant_id'          => $tenant->id,
            'periode_tahun'      => 2026,
            'periode_bulan'      => 11,
            'tanggal_catat'      => '2026-11-08',
            'stand_awal_air'     => 500.000,
            'stand_akhir_air'    => 450.000, // Mundur!
        ]);
        $response->assertSessionHas('error');
    }

    public function test_super_admin_can_input_stand_mundur_with_special_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = Tenant::create([
            'nama_tenant'         => 'Unit 35 DE',
            'kode_tenant'         => 'TNT-35-DE',
            'lantai'              => '35',
            'nomor_unit'          => 'DE',
            'nomor_meter_water'   => 'PAM-35-DE',
            'is_active'           => true,
        ]);

        // Super Admin inputs stand mundur with custom pemakaian & keterangan
        $response = $this->actingAs($admin)->post(route('billing.water.store'), [
            'tenant_id'       => $tenant->id,
            'periode_tahun'   => 2026,
            'periode_bulan'   => 11,
            'tanggal_catat'   => '2026-11-08',
            'stand_awal'      => 9990.000,
            'stand_akhir'     => 15.000, // Rollover meter
            'pemakaian'       => 25.000, // Manual pemakaian override
            'keterangan'      => 'Meter Rollover lewat 9999',
            'is_special_case' => 1,
        ]);

        $response->assertRedirect(route('billing.water.index', ['year' => 2026, 'month' => 11]));
        $response->assertSessionHas('success');

        $reading = TenantWaterReading::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($reading);
        $this->assertTrue($reading->is_special_case);
        $this->assertEquals(25.000, (float)$reading->pemakaian);
        $this->assertEquals('Meter Rollover lewat 9999', $reading->keterangan);
    }

    public function test_super_admin_can_bulk_import_water_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $csvContent = implode("\n", [
            ",,RESIDENCES METER READING WATER,,,,,,,,,,,,,,,",
            ",,BULAN   : OCTOBER 2026,,,,,,,,Daftar EV Charging Tenant,,,,,,,",
            ",No,Floor,Unit,AIR,,,Keterangan,,,No.,Unit,,,,,,",
            ",,,,Awal,Akhir,Pemakaian,,,,,,,,,,,",
            ",1,12,A1,371.829,375.535,3.706,,,,,,,,,,,",
            ",33,19,D,1798.736,1798.736,0,Segel,,,,,,,,,,",
            ",20,17/ CAR ,DE,,,0,,,,,,,,,,,", // Baris EV car harus di-skip di water
        ]);

        $response = $this->actingAs($admin)->post(route('billing.water.bulk.store'), [
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'raw_csv'       => $csvContent,
        ]);

        $response->assertRedirect(route('billing.water.index', ['year' => 2026, 'month' => 10]));
        $response->assertSessionHas('success');

        // Unit 12 A1 tersimpan
        $tenant12 = Tenant::where('lantai', '12')->where('nomor_unit', 'A1')->first();
        $this->assertNotNull($tenant12);
        $r12 = TenantWaterReading::where('tenant_id', $tenant12->id)->first();
        $this->assertNotNull($r12);
        $this->assertEquals(371.829, (float)$r12->stand_awal);
        $this->assertEquals(375.535, (float)$r12->stand_akhir);
        $this->assertEquals(3.706, (float)$r12->pemakaian);

        // Unit 19 D tersimpan dengan status kendala 'Segel'
        $tenant19 = Tenant::where('lantai', '19')->where('nomor_unit', 'D')->first();
        $this->assertNotNull($tenant19);
        $r19 = TenantWaterReading::where('tenant_id', $tenant19->id)->first();
        $this->assertNotNull($r19);
        $this->assertEquals('Segel', $r19->keterangan);
        $this->assertTrue($r19->is_special_case);
    }

    public function test_non_super_admin_cannot_access_bulk_water_import(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $response = $this->actingAs($teknisi)->get(route('billing.water.bulk.create'));
        $response->assertForbidden();

        $response = $this->actingAs($teknisi)->post(route('billing.water.bulk.store'), [
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'raw_csv'       => '1,12,A1,100,105,5',
        ]);
        $response->assertForbidden();
    }

    public function test_export_water_csv_and_excel_match_october_format(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $tenant = Tenant::create([
            'nama_tenant'    => 'Unit 12 A1',
            'kode_tenant'    => 'TNT-12-A1',
            'lantai'         => '12',
            'nomor_unit'     => 'A1',
            'is_active'      => true,
        ]);

        TenantWaterReading::create([
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'stand_awal'    => 371.829,
            'stand_akhir'   => 375.535,
            'pemakaian'     => 3.706,
        ]);

        // 1. Export Excel
        $response = $this->actingAs($admin)->get(route('billing.water.excel', ['year' => 2026, 'month' => 10]));
        $response->assertOk();
        $response->assertSee('RESIDENCES METER READING WATER');
        $response->assertSee('371.829');
        $response->assertSee('375.535');
        $response->assertSee('T O T A L');
        $response->assertSee('Frida');
        $response->assertSee('Triyoto');

        // 2. Export CSV
        $response = $this->actingAs($admin)->get(route('billing.water.csv', ['year' => 2026, 'month' => 10]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('RESIDENCES METER READING WATER');
        $response->assertSee('371.829');
    }

    public function test_previous_water_stand_is_auto_filled_for_november(): void
    {
        $tenant = Tenant::create([
            'nama_tenant' => 'Unit 15 A1',
            'kode_tenant' => 'TNT-15-A1',
            'lantai'      => '15',
            'nomor_unit'  => 'A1',
            'is_active'   => true,
        ]);

        TenantWaterReading::create([
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 10,
            'tanggal_catat' => '2026-10-31',
            'stand_awal'    => 4290.267,
            'stand_akhir'   => 4290.369,
            'pemakaian'     => 0.102,
        ]);

        // Stand awal bulan November harus otomatis mengambil stand akhir bulan Oktober
        $standAwalNov = $tenant->getPreviousWaterStand(2026, 11);
        $this->assertEquals(4290.369, $standAwalNov);
    }

    public function test_teknisi_must_upload_photo_for_energy_and_water(): void
    {
        // Pastikan sesi terbuka
        $setting = BillingPeriodSetting::current();
        $setting->update([
            'is_open'      => true,
            'active_year'  => 2026,
            'active_month' => 11,
        ]);

        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $tenant  = Tenant::create([
            'nama_tenant' => 'Unit 18 A1',
            'kode_tenant' => 'TNT-18-A1',
            'lantai'      => '18',
            'nomor_unit'  => 'A1',
            'is_active'   => true,
        ]);

        // 1. Submit Listrik tanpa foto -> GAGAL (session error)
        $responseE = $this->actingAs($teknisi)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 100,
            'stand_akhir'   => 120,
        ]);
        $responseE->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_energy_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11]);

        // 2. Submit Listrik DENGAN foto -> BERHASIL
        $fileE = $this->createFakePng('meter_listrik.png');
        $responseEOk = $this->actingAs($teknisi)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 100,
            'stand_akhir'   => 120,
            'foto_meter'    => $fileE,
        ]);
        $responseEOk->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tenant_energy_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11, 'stand_akhir' => 120]);

        // 3. Submit Air tanpa foto -> GAGAL
        $responseW = $this->actingAs($teknisi)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 50.000,
            'stand_akhir'   => 55.500,
        ]);
        $responseW->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_water_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11]);

        // 4. Submit Air DENGAN foto -> BERHASIL
        $fileW = $this->createFakePng('meter_air.png');
        $responseWOk = $this->actingAs($teknisi)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 50.000,
            'stand_akhir'   => 55.500,
            'foto_meter'    => $fileW,
        ]);
        $responseWOk->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tenant_water_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11, 'stand_akhir' => 55.500]);
    }

    public function test_super_admin_can_submit_without_photo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = Tenant::create([
            'nama_tenant' => 'Unit 19 B2',
            'kode_tenant' => 'TNT-19-B2',
            'lantai'      => '19',
            'nomor_unit'  => 'B2',
            'is_active'   => true,
        ]);

        // Super Admin input Listrik tanpa foto -> BERHASIL
        $responseE = $this->actingAs($admin)->post(route('billing.energy.store'), [
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 200,
            'stand_akhir'   => 230,
        ]);
        $responseE->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tenant_energy_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11, 'stand_akhir' => 230]);

        // Super Admin input Air tanpa foto -> BERHASIL
        $responseW = $this->actingAs($admin)->post(route('billing.water.store'), [
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 75.000,
            'stand_akhir'   => 80.200,
        ]);
        $responseW->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tenant_water_readings', ['tenant_id' => $tenant->id, 'periode_bulan' => 11, 'stand_akhir' => 80.200]);
    }

    public function test_quick_input_enforces_photo_for_teknisi_but_optional_for_super_admin(): void
    {
        $setting = BillingPeriodSetting::current();
        $setting->update([
            'is_open'      => true,
            'active_year'  => 2026,
            'active_month' => 11,
        ]);

        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $admin   = User::factory()->create(['role' => 'admin']);
        $tenant  = Tenant::create([
            'nama_tenant' => 'Unit 20 C1',
            'kode_tenant' => 'TNT-20-C1',
            'lantai'      => '20',
            'nomor_unit'  => 'C1',
            'is_active'   => true,
        ]);

        // Teknisi submit Quick Input tanpa foto -> HTTP 422 JSON
        $responseTeknisi = $this->actingAs($teknisi)->postJson(route('billing.quick-input.store'), [
            'tenant_id'           => $tenant->id,
            'periode_tahun'       => 2026,
            'periode_bulan'       => 11,
            'tanggal_catat'       => '2026-11-05',
            'stand_awal_listrik'  => 100,
            'stand_akhir_listrik' => 150,
            'stand_awal_air'      => 10,
            'stand_akhir_air'     => 15,
        ]);
        $responseTeknisi->assertStatus(422);
        $responseTeknisi->assertJsonFragment(['success' => false]);

        // Super Admin submit Quick Input tanpa foto -> HTTP 200 JSON Success
        $responseAdmin = $this->actingAs($admin)->postJson(route('billing.quick-input.store'), [
            'tenant_id'           => $tenant->id,
            'periode_tahun'       => 2026,
            'periode_bulan'       => 11,
            'tanggal_catat'       => '2026-11-05',
            'stand_awal_listrik'  => 100,
            'stand_akhir_listrik' => 150,
            'stand_awal_air'      => 10,
            'stand_akhir_air'     => 15,
        ]);
        $responseAdmin->assertOk();
        $responseAdmin->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('tenant_energy_readings', ['tenant_id' => $tenant->id, 'stand_akhir' => 150]);
        $this->assertDatabaseHas('tenant_water_readings', ['tenant_id' => $tenant->id, 'stand_akhir' => 15]);
    }

    public function test_photo_gallery_page_and_zip_download_and_print_report(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $tenant = Tenant::create([
            'nama_tenant' => 'Unit 21 D1',
            'kode_tenant' => 'TNT-21-D1',
            'lantai'      => '21',
            'nomor_unit'  => 'D1',
            'is_active'   => true,
        ]);

        // Buat dummy foto di storage fake
        $fakeImgPath = $this->createFakePng('kwh_21d1.png')->store('billing/energy', 'public');

        TenantEnergyReading::create([
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-05',
            'stand_awal'    => 500,
            'stand_akhir'   => 540,
            'pemakaian'     => 40,
            'foto_meter'    => $fakeImgPath,
        ]);

        // 1. Galeri Foto
        $responseGaleri = $this->actingAs($admin)->get(route('billing.photos', ['year' => 2026, 'month' => 11]));
        $responseGaleri->assertOk();
        $responseGaleri->assertSee('Galeri &amp; Backup Bukti Foto Meteran', false);
        $responseGaleri->assertSee('Unit 21 D1');

        // 2. Export ZIP Foto
        $responseZip = $this->actingAs($admin)->get(route('billing.photos.zip', ['year' => 2026, 'month' => 11]));
        $responseZip->assertOk();
        $this->assertTrue(str_contains($responseZip->headers->get('content-type') ?? '', 'zip') || str_contains($responseZip->headers->get('content-disposition') ?? '', '.zip'));

        // 3. Print Report / Lembar Bukti Fisik
        $responsePrint = $this->actingAs($admin)->get(route('billing.photos.print', ['year' => 2026, 'month' => 11, 'tenant_id' => $tenant->id]));
        $responsePrint->assertOk();
        $responsePrint->assertSee('Lembar Bukti Fisik Meteran Tenant');
        $responsePrint->assertSee('Unit 21 D1');
        $responsePrint->assertSee('540');
    }

    public function test_kelola_tenant_restricted_to_super_admin_and_supervisor(): void
    {
        $superAdmin = User::factory()->create(['role' => 'admin']);
        $supervisor = User::factory()->create(['role' => 'eng_spv']);
        $teknisi    = User::factory()->create(['role' => 'teknisi']);
        $engAdmin   = User::factory()->create(['role' => 'eng_admin']);
        $user       = User::factory()->create(['role' => 'user']);

        // 1. Super Admin diizinkan (200 OK)
        $this->actingAs($superAdmin)->get(route('billing.tenants.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('billing.tenants.create'))->assertOk();

        // 2. Supervisor diizinkan (200 OK)
        $this->actingAs($supervisor)->get(route('billing.tenants.index'))->assertOk();
        $this->actingAs($supervisor)->get(route('billing.tenants.create'))->assertOk();

        // 3. Teknisi DITOLAK (403 Forbidden)
        $this->actingAs($teknisi)->get(route('billing.tenants.index'))->assertForbidden();
        $this->actingAs($teknisi)->get(route('billing.tenants.create'))->assertForbidden();
        $this->actingAs($teknisi)->post(route('billing.tenants.store'), [
            'nama_tenant' => 'Unauthorized Tenant',
            'lantai'      => '5',
        ])->assertForbidden();

        // 4. Role lain (eng_admin, user) DITOLAK (403 Forbidden)
        $this->actingAs($engAdmin)->get(route('billing.tenants.index'))->assertForbidden();
        $this->actingAs($user)->get(route('billing.tenants.index'))->assertForbidden();
    }

    public function test_bukti_foto_auto_populated_from_energy_and_water_usage(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = Tenant::create([
            'nama_tenant'         => 'Tenant Resto 88',
            'kode_tenant'         => 'TNT-RESTO-88',
            'lantai'              => '10',
            'nomor_unit'          => 'R88',
            'nomor_meter_listrik' => 'KWH-R88',
            'nomor_meter_air'     => 'PAM-R88',
            'is_active'           => true,
        ]);

        $fakePhotoE = $this->createFakePng('resto_kwh.png')->store('billing/energy', 'public');
        $fakePhotoW = $this->createFakePng('resto_water.png')->store('billing/water', 'public');

        // Input Energy
        TenantEnergyReading::create([
            'tenant_id'     => $tenant->id,
            'meter_type'    => 'regular',
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 12500.00,
            'stand_akhir'   => 13150.00,
            'pemakaian'     => 650.00,
            'foto_meter'    => $fakePhotoE,
        ]);

        // Input Water
        TenantWaterReading::create([
            'tenant_id'     => $tenant->id,
            'periode_tahun' => 2026,
            'periode_bulan' => 11,
            'tanggal_catat' => '2026-11-08',
            'stand_awal'    => 120.450,
            'stand_akhir'   => 145.850,
            'pemakaian'     => 25.400,
            'foto_meter'    => $fakePhotoW,
        ]);

        // 1. Galeri Bukti Foto otomatis memuat data persis dari energy & water reading
        $resGaleri = $this->actingAs($admin)->get(route('billing.photos', ['year' => 2026, 'month' => 11]));
        $resGaleri->assertOk();
        $resGaleri->assertSee('Tenant Resto 88');
        $resGaleri->assertSee('13.150,00'); // Stand akhir listrik
        $resGaleri->assertSee('650,00');    // Pemakaian listrik
        $resGaleri->assertSee('145,850');   // Stand akhir air
        $resGaleri->assertSee('25,400');    // Pemakaian air

        // 2. Lembar Cetak Bukti Fisik otomatis memuat data persis
        $resPrint = $this->actingAs($admin)->get(route('billing.photos.print', ['year' => 2026, 'month' => 11, 'tenant_id' => $tenant->id]));
        $resPrint->assertOk();
        $resPrint->assertSee('Tenant Resto 88');
        $resPrint->assertSee('12.500,00'); // Stand awal
        $resPrint->assertSee('13.150,00'); // Stand akhir
        $resPrint->assertSee('650,00');    // Pemakaian listrik
        $resPrint->assertSee('120,450');   // Stand awal air
        $resPrint->assertSee('145,850');   // Stand akhir air
        $resPrint->assertSee('25,400');    // Pemakaian air

        // 3. Export ZIP Foto otomatis memuat kedua foto & manifest CSV
        $resZip = $this->actingAs($admin)->get(route('billing.photos.zip', ['year' => 2026, 'month' => 11]));
        $resZip->assertOk();
        $this->assertEquals('application/zip', $resZip->headers->get('content-type'));
    }
}
