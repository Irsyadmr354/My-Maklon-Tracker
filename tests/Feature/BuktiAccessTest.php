<?php

namespace Tests\Feature;

use App\Models\Bukti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuktiAccessTest extends TestCase
{
    use RefreshDatabase;

    private function buatBukti(User $owner): Bukti
    {
        Storage::fake('bukti');
        Storage::disk('bukti')->put('b1.pdf', 'isi-bukti-pdf');

        return Bukti::create([
            'user_id' => $owner->id,
            'step' => 2,
            'status' => 'done',
            'path' => 'bukti/b1.pdf',
        ]);
    }

    public function test_owner_bisa_akses_bukti(): void
    {
        $owner = User::factory()->create();
        $bukti = $this->buatBukti($owner);

        $response = $this->actingAs($owner)->get(route('bukti.show', $bukti->id));

        $response->assertStatus(200);
    }

    public function test_user_lain_ditolak_403(): void
    {
        $owner = User::factory()->create();
        $lain = User::factory()->create();
        $bukti = $this->buatBukti($owner);

        $response = $this->actingAs($lain)->get(route('bukti.show', $bukti->id));

        $response->assertStatus(403);
    }

    public function test_guest_redirect_ke_login(): void
    {
        $owner = User::factory()->create();
        $bukti = $this->buatBukti($owner);

        $response = $this->get(route('bukti.show', $bukti->id));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_bisa_akses_bukti_customer(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $bukti = $this->buatBukti($owner);

        $response = $this->actingAs($admin)->get(route('bukti.show', $bukti->id));

        $response->assertStatus(200);
    }
}
