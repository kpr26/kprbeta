<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualLogsheetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_super_admin_can_access_manual_listrik_and_air_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $responseListrik = $this->actingAs($admin)->get(route('logsheet.manual.listrik'));
        $responseListrik->assertStatus(200);
        $responseListrik->assertSee('AMR PLN');

        $responseAir = $this->actingAs($admin)->get(route('logsheet.manual.air'));
        $responseAir->assertStatus(200);
        $responseAir->assertSee('Input Manual Logsheet Air');
    }

    public function test_teknisi_cannot_access_manual_input_pages(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $this->actingAs($teknisi)
            ->get(route('logsheet.manual.listrik'))
            ->assertStatus(403);

        $this->actingAs($teknisi)
            ->get(route('logsheet.manual.air'))
            ->assertStatus(403);
    }

    public function test_super_admin_can_input_manual_listrik_for_past_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pastDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('logsheet.manual.listrik.store'), [
            'tanggal'    => $pastDate,
            'stand_lwbp' => 1250.50,
            'stand_wbp'  => 450.25,
            'catatan'    => 'Input susulan tanggal terlewat',
        ]);

        $response->assertRedirect();
        $reading = MeterReading::whereDate('tanggal', $pastDate)->first();
        $this->assertNotNull($reading);
        $this->assertEquals(1250.50, $reading->stand_lwbp);
        $this->assertEquals(450.25, $reading->stand_wbp);
    }

    public function test_super_admin_can_input_manual_air_for_past_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pastDate = now()->subDays(3)->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('logsheet.manual.air.store'), [
            'tanggal'   => $pastDate,
            'stand_air' => 315.75,
            'catatan'   => 'Input susulan air tanggal terlewat',
        ]);

        $response->assertRedirect();
        $reading = MeterReading::whereDate('tanggal', $pastDate)->first();
        $this->assertNotNull($reading);
        $this->assertEquals(315.75, $reading->stand_air);
    }

    public function test_manual_input_preserves_other_utility_data_on_same_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pastDate = now()->subDays(2)->format('Y-m-d');

        // Pertama input listrik
        $this->actingAs($admin)->post(route('logsheet.manual.listrik.store'), [
            'tanggal'    => $pastDate,
            'stand_lwbp' => 1000.00,
            'stand_wbp'  => 500.00,
        ]);

        // Kemudian input air pada tanggal yang sama
        $this->actingAs($admin)->post(route('logsheet.manual.air.store'), [
            'tanggal'   => $pastDate,
            'stand_air' => 88.50,
        ]);

        $reading = MeterReading::whereDate('tanggal', $pastDate)->first();
        $this->assertNotNull($reading);
        $this->assertEquals(1000.00, $reading->stand_lwbp);
        $this->assertEquals(500.00, $reading->stand_wbp);
        $this->assertEquals(88.50, $reading->stand_air);
    }

    public function test_manual_input_validates_stand_against_previous_and_next_readings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Data tanggal 10
        MeterReading::create([
            'tanggal'    => '2026-10-10',
            'user_id'    => $admin->id,
            'stand_lwbp' => 1000,
            'stand_wbp'  => 500,
            'stand_air'  => 50,
        ]);

        // Data tanggal 12
        MeterReading::create([
            'tanggal'    => '2026-10-12',
            'user_id'    => $admin->id,
            'stand_lwbp' => 1200,
            'stand_wbp'  => 600,
            'stand_air'  => 70,
        ]);

        // Input manual tanggal 11 dengan stand lebih kecil dari tanggal 10 (harus ditolak)
        $respTooSmall = $this->actingAs($admin)->from(route('logsheet.manual.listrik'))->post(route('logsheet.manual.listrik.store'), [
            'tanggal'    => '2026-10-11',
            'stand_lwbp' => 950, // < 1000
            'stand_wbp'  => 550,
        ]);
        $respTooSmall->assertSessionHasErrors(['stand_lwbp']);

        // Input manual tanggal 11 dengan stand lebih besar dari tanggal 12 (harus ditolak)
        $respTooBig = $this->actingAs($admin)->from(route('logsheet.manual.air'))->post(route('logsheet.manual.air.store'), [
            'tanggal'   => '2026-10-11',
            'stand_air' => 85, // > 70
        ]);
        $respTooBig->assertSessionHasErrors(['stand_air']);

        // Input manual tanggal 11 dengan stand valid di antara tanggal 10 dan 12 (harus diterima)
        $respValid = $this->actingAs($admin)->post(route('logsheet.manual.listrik.store'), [
            'tanggal'    => '2026-10-11',
            'stand_lwbp' => 1100,
            'stand_wbp'  => 550,
        ]);
        $respValid->assertSessionHasNoErrors();
        $reading = MeterReading::whereDate('tanggal', '2026-10-11')->first();
        $this->assertNotNull($reading);
        $this->assertEquals(1100, $reading->stand_lwbp);
        $this->assertEquals(550, $reading->stand_wbp);
    }

    public function test_reference_api_returns_correct_adjacent_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        MeterReading::create([
            'tanggal'    => '2026-10-01',
            'user_id'    => $admin->id,
            'stand_lwbp' => 100,
            'stand_wbp'  => 50,
            'stand_air'  => 10,
        ]);

        MeterReading::create([
            'tanggal'    => '2026-10-05',
            'user_id'    => $admin->id,
            'stand_lwbp' => 200,
            'stand_wbp'  => 100,
            'stand_air'  => 20,
        ]);

        $response = $this->actingAs($admin)->getJson(route('logsheet.manual.reference', [
            'tanggal' => '2026-10-03',
            'type'    => 'listrik',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'tanggal' => '2026-10-03',
            'exists'  => false,
            'prev'    => [
                'tanggal'    => '2026-10-01',
                'stand_lwbp' => 100,
            ],
            'next'    => [
                'tanggal'    => '2026-10-05',
                'stand_lwbp' => 200,
            ],
        ]);
    }
}
