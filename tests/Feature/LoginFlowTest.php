<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_login_membuat_user_baru_dan_redirect_ke_tracker(): void
    {
        $response = $this->post('/login', [
            'email' => 'pendaftar-baru@example.com',
            'no_hp' => '081234567890',
        ]);

        $response->assertRedirect(route('tracker.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'pendaftar-baru@example.com',
            'role' => 'user',
        ]);
    }

    public function test_nomor_admin_dapat_role_admin_dan_redirect_ke_admin_index(): void
    {
        config(['maklon.admin_phone' => '089999999999']);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'no_hp' => '089999999999',
        ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_user_biasa_get_admin_ditolak_403(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_get_admin_diperbolehkan(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_guest_order_tracker_redirect_ke_login(): void
    {
        $response = $this->get('/order-tracker');

        $response->assertRedirect(route('login'));
    }
}
