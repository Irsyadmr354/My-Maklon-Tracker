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

    private const PESAN_GENERIK = 'Email atau kata sandi salah.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_login_sukses_dengan_password_redirect_ke_tracker(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'sandra8Kuat',
        ]);

        $response->assertRedirect(route('tracker.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_pesan_generik_dan_tidak_authenticated(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->from(route('login.form'))->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'bukanSandi9',
        ]);

        $response->assertSessionHasErrors(['login' => self::PESAN_GENERIK]);
        $this->assertGuest();
    }

    public function test_email_tak_terdaftar_pesan_identik_dengan_kasus_password_salah(): void
    {
        $response = $this->from(route('login.form'))->post('/login', [
            'email' => 'tidak-ada@example.com',
            'password' => 'sandiapapun8',
        ]);

        $response->assertSessionHasErrors(['login' => self::PESAN_GENERIK]);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'tidak-ada@example.com',
        ]);
    }

    public function test_aktivasi_pertama_no_hp_cocok_set_password_db(): void
    {
        User::factory()->tanpaPassword()->create([
            'email' => 'lama@example.com',
            'no_hp' => '081234567890',
        ]);

        $response = $this->post('/login', [
            'email' => 'lama@example.com',
            'password' => 'aktivasi8Ok',
            'no_hp' => '081234567890',
        ]);

        $response->assertRedirect(route('tracker.index'));
        $this->assertAuthenticated();

        $fresh = User::where('email', 'lama@example.com')->firstOrFail();
        $this->assertNotNull($fresh->password);
        $this->assertTrue(Hash::check('aktivasi8Ok', $fresh->password));
    }

    public function test_aktivasi_no_hp_salah_gagal_generik_dan_password_tetap_null(): void
    {
        User::factory()->tanpaPassword()->create([
            'email' => 'lama@example.com',
            'no_hp' => '081234567890',
        ]);

        $response = $this->from(route('login.form'))->post('/login', [
            'email' => 'lama@example.com',
            'password' => 'aktivasi8Ok',
            'no_hp' => '089999999999',
        ]);

        $response->assertSessionHasErrors(['login' => self::PESAN_GENERIK]);
        $this->assertGuest();

        $this->assertNull(User::where('email', 'lama@example.com')->firstOrFail()->password);
    }

    public function test_nomor_admin_mendapat_role_admin(): void
    {
        config(['maklon.admin_phone' => '089999999999']);

        $user = User::factory()->create([
            'email' => 'calonadmin@example.com',
            'password' => Hash::make('sandra8Kuat'),
        ]);

        $response = $this->post('/login', [
            'email' => 'calonadmin@example.com',
            'password' => 'sandra8Kuat',
            'no_hp' => '089999999999',
        ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_aktivasi_nomor_biasa_role_turun_user(): void
    {
        config(['maklon.admin_phone' => '089999999999']);

        User::factory()->tanpaPassword()->admin()->create([
            'email' => 'admlama@example.com',
            'no_hp' => '081234567890',
        ]);

        $this->post('/login', [
            'email' => 'admlama@example.com',
            'password' => 'aktivasi8Ok',
            'no_hp' => '081234567890',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admlama@example.com',
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
