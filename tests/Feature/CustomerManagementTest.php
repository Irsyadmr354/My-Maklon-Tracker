<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

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

    public function test_admin_menambah_customer(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/customers', [
            'email' => 'customer-baru@example.com',
            'no_hp' => '081234567891',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Customer ditambahkan.');

        $baru = User::where('email', 'customer-baru@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $baru->password));
        $this->assertSame('user', $baru->role);
    }

    public function test_customer_baru_bisa_login_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/customers', [
            'email' => 'customer-baru@example.com',
            'no_hp' => '081234567891',
            'password' => 'rahasia123',
        ]);

        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => 'customer-baru@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('tracker.index'));

        $baru = User::where('email', 'customer-baru@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($baru);
    }
}
