<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_profile_edit()
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
            'username' => 'teknisi_test',
            'name' => 'Teknisi Test',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Informasi Akun');
        $response->assertSee('Foto Profil');
        $response->assertSee('Tanda Tangan Digital');
        $response->assertSee('Ubah Password');
        $response->assertSee('profile-mobile-swipe-indicator');
        $response->assertSee('id="mobileSidebarToggle"', false);
        $response->assertSee('id="mobileUserBtn"', false);
        $response->assertSee('window.openMobileSidebar', false);
        $response->assertSee('window.toggleMobileUserMenu', false);
    }

    public function test_superadmin_can_access_profile_edit_with_admin_tabs()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_test',
            'name' => 'Admin Test',
        ]);

        $response = $this->actingAs($admin)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Reset Tanda Tangan');
        $response->assertSee('Kelola Akun');
    }
}
