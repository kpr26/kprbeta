<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsappSetting;
use App\Services\ExecutiveSummaryService;
use App\Services\FonnteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_summary_service_detects_unrecorded_logsheets(): void
    {
        $service = app(ExecutiveSummaryService::class);
        $data = $service->getData('2026-10-29');

        $this->assertFalse($data['all_completed']);
        $this->assertGreaterThan(0, $data['unrecorded_count']);
        $this->assertContains('Listrik AMR PLN Gedung', $data['unrecorded_items']);
        $this->assertContains('Air PDAM Gedung', $data['unrecorded_items']);

        $message = $service->formatWhatsAppMessage('2026-10-29');
        $this->assertStringContainsString('EXECUTIVE SUMMARY & LOGSHEET PULSE', $message);
        $this->assertStringContainsString('PERINGATAN: LOGSHEET BELUM LENGKAP', $message);
        $this->assertStringContainsString('Listrik AMR PLN Gedung', $message);
    }

    public function test_artisan_command_dry_run_executes_successfully(): void
    {
        $this->artisan('whatsapp:send-daily-summary', ['--dry-run' => true])
            ->expectsOutputToContain('MODE DRY-RUN AKTIF')
            ->expectsOutputToContain('EXECUTIVE SUMMARY & LOGSHEET PULSE')
            ->assertExitCode(0);
    }

    public function test_fonnte_service_sends_http_request(): void
    {
        config([
            'services.fonnte.token'        => 'dummy_token_123',
            'services.fonnte.target_group' => '120363024823812933@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['120363024823812933@g.us'],
                'detail' => 'success',
            ], 200),
        ]);

        $fonnteService = app(FonnteService::class);
        $result = $fonnteService->sendMessage(null, 'Test message');

        $this->assertTrue($result['success']);
        $this->assertNull($result['error']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'dummy_token_123')
                && $request['target'] === '120363024823812933@g.us';
        });
    }

    public function test_superadmin_can_access_whatsapp_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($superadmin)->get(route('admin.whatsapp.settings'));
        $response->assertOk()
            ->assertViewIs('admin.whatsapp.settings')
            ->assertSee('Pengaturan Notifikasi WhatsApp');

        $responseAdmin = $this->actingAs($admin)->get(route('admin.whatsapp.settings'));
        $responseAdmin->assertOk()
            ->assertViewIs('admin.whatsapp.settings');
    }

    public function test_sidebar_displays_whatsapp_menu_for_superadmin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('gm.monitoring'));
        $response->assertOk();
        $response->assertSee('Pengaturan WhatsApp');
        $response->assertSee(route('admin.whatsapp.settings'));

        // Check GM does not see it
        $gm = User::factory()->create(['role' => 'gm']);
        $responseGm = $this->actingAs($gm)->get(route('gm.monitoring'));
        $responseGm->assertOk();
        $responseGm->assertDontSee(route('admin.whatsapp.settings'));
    }

    public function test_gm_and_other_roles_cannot_access_whatsapp_settings(): void
    {
        $gm = User::factory()->create(['role' => 'gm']);
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $spv = User::factory()->create(['role' => 'eng_spv']);

        $this->actingAs($gm)->get(route('admin.whatsapp.settings'))->assertForbidden();
        $this->actingAs($teknisi)->get(route('admin.whatsapp.settings'))->assertForbidden();
        $this->actingAs($spv)->get(route('admin.whatsapp.settings'))->assertForbidden();
    }

    public function test_superadmin_can_update_whatsapp_settings(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $payload = [
            'fonnte_token'      => '7vPVyswvCqqpoUqiPMa2_custom',
            'target_group_id'   => '120363999999999@g.us',
            'target_group_name' => 'Grup Testing Unit',
            'scheduled_time'    => '18:30',
            'is_active'         => '1',
            'send_to_group'     => '1',
            'send_to_numbers'   => '1',
            'header_template'   => '🏢 *HEADER KUSTOM*',
            'reminder_template' => '👉 Harap segera isi logsheet!',
            'success_template'  => 'Semua beres!',
            'footer_template'   => 'Footer kustom',
            'phone_numbers'     => [
                ['number' => '08123456789', 'name' => 'GM Bpk. Andi'],
                ['number' => '08987654321', 'name' => 'Spv Agus'],
            ],
        ];

        $response = $this->actingAs($superadmin)->put(route('admin.whatsapp.update'), $payload);

        $response->assertRedirect(route('admin.whatsapp.settings'))
            ->assertSessionHas('success');

        $setting = WhatsappSetting::getSetting();
        $this->assertEquals('7vPVyswvCqqpoUqiPMa2_custom', $setting->getFonnteToken());
        $this->assertEquals('120363999999999@g.us', $setting->target_group_id);
        $this->assertEquals('18:30', $setting->scheduled_time);
        $this->assertCount(2, $setting->target_phone_numbers);
        $this->assertEquals('628123456789', $setting->target_phone_numbers[0]['number']);
    }

    public function test_superadmin_can_trigger_test_send(): void
    {
        config([
            'services.fonnte.token' => 'dummy_token_123',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'detail' => 'success',
            ], 200),
        ]);

        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post(route('admin.whatsapp.test-send'), [
            'date'          => '2026-10-29',
            'send_mode'     => 'custom',
            'custom_target' => '08123456789',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_superadmin_can_save_multiple_groups_with_select_unselect_options(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $payload = [
            'scheduled_time'    => '18:00',
            'is_active'         => '1',
            'send_to_group'     => '1',
            'send_to_numbers'   => '1',
            'groups'            => [
                ['id' => '120363001@g.us', 'name' => 'Grup Engineering', 'is_active' => '1'],
                ['id' => '120363002@g.us', 'name' => 'Grup Manajemen', 'is_active' => '1'],
                ['id' => '120363003@g.us', 'name' => 'Grup Vendor', 'is_active' => '0'], // unselected
            ],
            'phone_numbers'     => [
                ['number' => '08121111111', 'name' => 'Pak GM', 'is_active' => '1'],
                ['number' => '08122222222', 'name' => 'Pak Spv', 'is_active' => '0'], // unselected
            ],
        ];

        $response = $this->actingAs($superadmin)->put(route('admin.whatsapp.update'), $payload);
        $response->assertRedirect(route('admin.whatsapp.settings'))
            ->assertSessionHas('success');

        $setting = WhatsappSetting::getSetting();

        // Check stored groups
        $this->assertCount(3, $setting->target_groups);
        $this->assertEquals('120363001@g.us', $setting->target_groups[0]['id']);
        $this->assertTrue($setting->target_groups[0]['is_active']);
        $this->assertFalse($setting->target_groups[2]['is_active']);

        // Check active targets logic
        $activeGroups = $setting->getActiveGroups();
        $this->assertCount(2, $activeGroups);
        $this->assertEquals('120363001@g.us', $activeGroups[0]['id']);
        $this->assertEquals('120363002@g.us', $activeGroups[1]['id']);

        $activePhones = $setting->getActivePhoneNumbers();
        $this->assertCount(1, $activePhones);
        $this->assertEquals('628121111111', $activePhones[0]['number']);

        $allTargets = $setting->getAllActiveTargets();
        $this->assertCount(3, $allTargets);
        $this->assertContains('120363001@g.us', $allTargets);
        $this->assertContains('120363002@g.us', $allTargets);
        $this->assertNotContains('120363003@g.us', $allTargets);
        $this->assertContains('628121111111', $allTargets);
        $this->assertNotContains('628122222222', $allTargets);
    }
}
