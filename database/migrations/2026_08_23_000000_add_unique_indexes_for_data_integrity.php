<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Menambahkan unique index demi integritas data:
// - users.email
// - progresses.user_id
// - bukti (user_id, step)
// Duplikat dihapus lebih dulu (survivor = id TERKECIL) agar pembuatan
// index tidak gagal pada data lama. Pendekatan query builder murni agar
// portabel antara MySQL dan sqlite.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus duplikat users.email, sisakan id terkecil.
        $emailDuplikat = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($emailDuplikat as $duplikat) {
            $idTetap = DB::table('users')
                ->where('email', $duplikat->email)
                ->min('id');

            DB::table('users')
                ->where('email', $duplikat->email)
                ->where('id', '!=', $idTetap)
                ->delete();
        }

        // Hapus duplikat progresses.user_id, sisakan id terkecil.
        $progressDuplikat = DB::table('progresses')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($progressDuplikat as $duplikat) {
            $idTetap = DB::table('progresses')
                ->where('user_id', $duplikat->user_id)
                ->min('id');

            DB::table('progresses')
                ->where('user_id', $duplikat->user_id)
                ->where('id', '!=', $idTetap)
                ->delete();
        }

        // Hapus duplikat bukti (user_id, step), sisakan id terkecil.
        $buktiDuplikat = DB::table('bukti')
            ->select('user_id', 'step')
            ->groupBy('user_id', 'step')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($buktiDuplikat as $duplikat) {
            $idTetap = DB::table('bukti')
                ->where('user_id', $duplikat->user_id)
                ->where('step', $duplikat->step)
                ->min('id');

            DB::table('bukti')
                ->where('user_id', $duplikat->user_id)
                ->where('step', $duplikat->step)
                ->where('id', '!=', $idTetap)
                ->delete();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('progresses', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('bukti', function (Blueprint $table) {
            $table->unique(['user_id', 'step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('progresses', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('bukti', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'step']);
        });
    }
};
