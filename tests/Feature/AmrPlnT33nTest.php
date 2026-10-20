<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AmrPlnT33nTest extends TestCase
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

    public function test_teknisi_can_input_amr_pln_t33n_with_single_photo(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $file = $this->createFakePng('amr_meter.png');

        $response = $this->actingAs($teknisi)->post(route('logsheet.store.listrik'), [
            'stand_bp'    => 1050.25,
            'stand_lbp1'  => 3200.50,
            'stand_lbp2'  => 1800.75,
            'stand_total' => 6051.50,
            'foto_kwh'    => $file,
            'catatan'     => 'Kondisi display AMR normal',
        ]);

        $response->assertRedirect(route('logsheet.hub'));

        $record = MeterReading::whereDate('tanggal', today())->first();
        $this->assertNotNull($record);
        $this->assertEquals(1050.25, $record->stand_bp);
        $this->assertEquals(3200.50, $record->stand_lbp1);
        $this->assertEquals(1800.75, $record->stand_lbp2);
        $this->assertEquals(6051.50, $record->stand_total);
        // Pastikan backwards compatibility sinkronisasi
        $this->assertEquals(1050.25, $record->stand_wbp);
        $this->assertEquals(5001.25, $record->stand_lwbp);
        $this->assertNotNull($record->foto_kwh);
        Storage::disk('public')->assertExists($record->foto_kwh);
    }

    public function test_hub_renders_amr_pln_t33n_and_compact_offline_pill(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($teknisi)->get(route('logsheet.hub'));
        $response->assertStatus(200);
        $response->assertSee('Logsheet AMR PLN T33N');
        $response->assertSee('kWh Meter (BP, LBP1, LBP2, Total)');
        $response->assertSee('Mode Offline Aktif');
    }

    public function test_super_admin_can_input_manual_amr_pln_t33n(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pastDate = now()->subDays(2)->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('logsheet.manual.listrik.store'), [
            'tanggal'     => $pastDate,
            'stand_bp'    => 800.00,
            'stand_lbp1'  => 2000.00,
            'stand_lbp2'  => 1500.00,
            'stand_total' => 4300.00,
        ]);

        $response->assertRedirect();
        $record = MeterReading::whereDate('tanggal', $pastDate)->first();
        $this->assertNotNull($record);
        $this->assertEquals(800.00, $record->stand_bp);
        $this->assertEquals(2000.00, $record->stand_lbp1);
        $this->assertEquals(1500.00, $record->stand_lbp2);
        $this->assertEquals(4300.00, $record->stand_total);
    }

    public function test_management_roles_can_view_recap_with_graphic_and_table_models(): void
    {
        $superAdmin = User::factory()->create(['role' => 'admin']);
        $engAdmin   = User::factory()->create(['role' => 'eng_admin']);
        $engSpv     = User::factory()->create(['role' => 'eng_spv']);

        // Buat data pembacaan meter berturut-turut untuk menguji pemakaian dan akumulatif
        $d1 = now()->startOfMonth()->format('Y-m-d');
        $d2 = now()->startOfMonth()->addDay()->format('Y-m-d');

        MeterReading::create([
            'tanggal'     => $d1,
            'user_id'     => $superAdmin->id,
            'stand_bp'    => 100,
            'stand_lbp1'  => 200,
            'stand_lbp2'  => 300,
            'stand_total' => 600,
            'stand_air'   => 50,
        ]);

        MeterReading::create([
            'tanggal'     => $d2,
            'user_id'     => $superAdmin->id,
            'stand_bp'    => 120, // pakai_bp = 20
            'stand_lbp1'  => 230, // pakai_lbp1 = 30
            'stand_lbp2'  => 350, // pakai_lbp2 = 50 -> pakai_lbp = 80
            'stand_total' => 700, // pakai_kwh = 100
            'stand_air'   => 65,  // pakai_air = 15
        ]);

        foreach ([$superAdmin, $engAdmin, $engSpv] as $user) {
            $response = $this->actingAs($user)->get(route('logsheet.index'));
            $response->assertStatus(200);

            // Verifikasi 2 model tampilan
            $response->assertSee('1. Model Grafik');
            $response->assertSee('2. Model Tabel');
            $response->assertSee('Tampilkan Keduanya');

            // Verifikasi elemen visual grafik paparan manajemen
            $response->assertSee('chartElecMgmt');
            $response->assertSee('chartProporsiMgmt');
            $response->assertSee('chartAirMgmt');
            $response->assertSee('Paparan Manajemen Engineering');

            // Verifikasi header tabel yang diminta user
            $response->assertSee('Stand BP');
            $response->assertSee('Stand LBP 1');
            $response->assertSee('Stand LBP 2');
            $response->assertSee('Total BP (Akum.)');
            $response->assertSee('Total LBP (Akum.)');
            $response->assertSee('Total Air (Akum.)');

            // Verifikasi bahwa '<th>Stand Total</th>' TIDAK ada di tabel
            $response->assertDontSee('<th>Stand Total</th>', false);
        }
    }

    public function test_excel_and_pdf_exports_work_for_management(): void
    {
        $engSpv = User::factory()->create(['role' => 'eng_spv']);

        MeterReading::create([
            'tanggal'     => today()->format('Y-m-d'),
            'user_id'     => $engSpv->id,
            'stand_bp'    => 100,
            'stand_lbp1'  => 200,
            'stand_lbp2'  => 300,
            'stand_total' => 600,
            'stand_air'   => 50,
        ]);

        $periode = today()->format('Y-m');

        // Test Excel
        $excelRes = $this->actingAs($engSpv)->get(route('logsheet.excel', ['periode' => $periode]));
        $excelRes->assertStatus(200);
        $excelRes->assertHeader('content-disposition');
        $excelRes->assertDontSee('<th>Stand Total</th>', false);

        // Test PDF
        $pdfRes = $this->actingAs($engSpv)->get(route('logsheet.pdf', ['periode' => $periode]));
        $pdfRes->assertStatus(200);
        $pdfRes->assertDontSee('<th>Stand Total</th>', false);
    }
}
