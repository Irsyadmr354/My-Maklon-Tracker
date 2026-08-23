<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_daftar_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/customers');

        $response->assertStatus(200);
        $response->assertSee($customer->email, false);
    }

    public function test_admin_membuka_halaman_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/customers/' . $customer->id);

        $response->assertStatus(200);
        $response->assertSee($customer->email, false);
    }

    public function test_admin_mengubah_progres_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $response = $this->actingAs($admin)->post('/progress/update', [
            'user_id' => $customer->id,
            'status1' => 'done',
            'tanggal1' => '2026-08-23',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('progresses', [
            'user_id' => $customer->id,
            'status1' => 'done',
        ]);
    }

    public function test_guest_tak_bisa_post_progres(): void
    {
        $response = $this->post('/progress/update', [
            'status1' => 'done',
            'tanggal1' => '2026-08-23',
        ]);

        $response->assertRedirect(route('login'));
    }
}
