<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_admin_ubah_no_hp_customer(): void
    {
        $customer = User::factory()->create(['no_hp' => '081111111111']);
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $customer), [
            'no_hp' => '082222222222',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'no_hp' => '082222222222']);
    }

    public function test_admin_ubah_password_customer(): void
    {
        $customer = User::factory()->create(['no_hp' => '081111111111', 'password' => Hash::make('lamaPass8')]);
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $customer), [
            'no_hp' => '081111111111',
            'password' => 'sandiBaru8Ok',
        ]);

        $response->assertRedirect(route('customers.index'));

        $fresh = User::findOrFail($customer->id);
        $this->assertTrue(Hash::check('sandiBaru8Ok', $fresh->password));
    }

    public function test_password_kosong_tidak_mengubah_password(): void
    {
        $customer = User::factory()->create(['no_hp' => '081111111111', 'password' => Hash::make('rahasia8Ok')]);
        $lamaHash = $customer->password;
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $customer), [
            'no_hp' => '081111111111',
        ]);

        $response->assertRedirect(route('customers.index'));

        $fresh = User::findOrFail($customer->id);
        $this->assertTrue(Hash::check('rahasia8Ok', $fresh->password));
    }

    public function test_non_admin_ditolak_403(): void
    {
        $customer = User::factory()->create(['no_hp' => '081111111111']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customers.akun', $customer), [
            'no_hp' => '082222222222',
        ]);

        $response->assertStatus(403);
    }

    public function test_no_hp_admin_ditolak(): void
    {
        $customer = User::factory()->create(['no_hp' => '081111111111']);
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $customer), [
            'no_hp' => '089999999999',
        ]);

        $response->assertSessionHasErrors(['no_hp']);
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'no_hp' => '081111111111']);
    }

    public function test_no_hp_admin_tidak_boleh_diubah(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $admin), [
            'no_hp' => '081122223333',
        ]);

        $response->assertSessionHasErrors(['no_hp']);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'no_hp' => '089999999999']);
    }

    public function test_no_hp_duplikat_ditolak(): void
    {
        $customerA = User::factory()->create(['no_hp' => '081111111111']);
        $customerB = User::factory()->create(['no_hp' => '082222222222']);
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('customers.index'))->actingAs($admin)->post(route('customers.akun', $customerB), [
            'no_hp' => '081111111111',
        ]);

        $response->assertSessionHasErrors(['no_hp']);
        $this->assertDatabaseHas('users', ['id' => $customerB->id, 'no_hp' => '082222222222']);
    }
}
