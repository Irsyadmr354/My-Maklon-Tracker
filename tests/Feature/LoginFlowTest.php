<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_GENERIK = 'Nomor HP atau kata sandi salah.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_login_sukses_dengan_password_redirect_ke_tracker(): void
    {
        $user = User::factory()->create([
            'no_hp' => '081110001111',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->post('/login', [
            'no_hp' => '081110001111',
            'password' => 'sandra8Kuat',
        ]);

        $response->assertRedirect(route('tracker.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_pesan_generik_dan_tidak_authenticated(): void
    {
        User::factory()->create([
            'no_hp' => '081110001111',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->from(route('login.form'))->post('/login', [
            'no_hp' => '081110001111',
            'password' => 'salahPass8',
        ]);

        $response->assertSessionHasErrors(['login' => self::PESAN_GENERIK]);
        $this->assertGuest();
    }

    public function test_no_hp_tak_terdaftar_pesan_identik_dengan_kasus_password_salah(): void
    {
        $response = $this->from(route('login.form'))->post('/login', [
            'no_hp' => '080000000001',
            'password' => 'sandiApapun8',
        ]);

        $response->assertSessionHasErrors(['login' => self::PESAN_GENERIK]);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'no_hp' => '080000000001',
        ]);
    }

    public function test_aktivasi_pertama_set_password_db(): void
    {
        User::factory()->tanpaPassword()->create([
            'no_hp' => '081234567890',
        ]);

        $response = $this->post('/login', [
            'no_hp' => '081234567890',
            'password' => 'aktivasi8Ok',
        ]);

        $response->assertRedirect(route('tracker.index'));
        $this->assertAuthenticated();

        $fresh = User::where('no_hp', '081234567890')->firstOrFail();
        $this->assertNotNull($fresh->password);
        $this->assertTrue(Hash::check('aktivasi8Ok', $fresh->password));
    }

    public function test_aktivasi_nomor_admin_mendapat_role_admin(): void
    {
        User::factory()->tanpaPassword()->create([
            'no_hp' => '089999999999',
        ]);

        $response = $this->post('/login', [
            'no_hp' => '089999999999',
            'password' => 'aktivasi8Ok',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('users', [
            'no_hp' => '089999999999',
            'role' => 'admin',
        ]);

        $fresh = User::where('no_hp', '089999999999')->firstOrFail();
        $this->assertSame('admin', $fresh->role);
    }

    public function test_nomor_admin_terdaftar_promosi_role_admin(): void
    {
        User::factory()->create([
            'no_hp' => '089999999999',
            'role' => 'user',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->post('/login', [
            'no_hp' => '089999999999',
            'password' => 'sandra8Kuat',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('users', [
            'no_hp' => '089999999999',
            'role' => 'admin',
        ]);
    }

    public function test_aktivasi_nomor_biasa_role_turun_user(): void
    {
        User::factory()->tanpaPassword()->admin()->create([
            'no_hp' => '081234567890',
        ]);

        $this->post('/login', [
            'no_hp' => '081234567890',
            'password' => 'aktivasi8Ok',
        ]);

        $this->assertDatabaseHas('users', [
            'no_hp' => '081234567890',
            'role' => 'user',
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

    public function test_guest_admin_redirect_ke_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_guest_order_tracker_redirect_ke_login(): void
    {
        $response = $this->get('/order-tracker');

        $response->assertRedirect(route('login'));
    }
}
