<?php

namespace Tests\Feature;

use App\Models\Bukti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_perubahan_status_menulis_progress_history(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        DB::table('progresses')->insert([
            'user_id' => $customer->id,
            'status2' => 'hold',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Bukti::create([
            'user_id' => $customer->id,
            'step' => 2,
            'status' => 'hold',
            'keterangan' => 'pembayaran',
        ]);

        $response = $this->actingAs($admin)->post('/progress/update', [
            'user_id' => $customer->id,
            'status2' => 'done',
            'tanggal2' => '2026-08-24',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('progress_histories', [
            'user_id' => $customer->id,
            'step' => 2,
            'status_lama' => 'hold',
            'status_baru' => 'done',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_tahap_tak_berubah_tidak_tulis_history_baru(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        DB::table('progresses')->insert([
            'user_id' => $customer->id,
            'status2' => 'done',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Bukti::create([
            'user_id' => $customer->id,
            'step' => 2,
            'status' => 'done',
            'keterangan' => 'pembayaran',
        ]);

        $this->actingAs($admin)->post('/progress/update', [
            'user_id' => $customer->id,
            'status2' => 'done',
            'tanggal2' => '2026-08-24',
        ]);

        $jumlah = DB::table('progress_histories')
            ->where('user_id', $customer->id)
            ->where('step', 2)
            ->count();

        $this->assertSame(0, $jumlah);
    }
}
