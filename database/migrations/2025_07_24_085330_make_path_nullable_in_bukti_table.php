<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// No-op: kolom path pada tabel bukti sudah nullable sejak pembuatan tabel
// (lihat 2025_07_21_081737_create_bukti_table.php).
// Pemanggilan ->change() dihapus karena membutuhkan doctrine/dbal
// yang tidak tersedia di project ini.
// File ini dipertahankan demi riwayat migrate yang sudah tercatat.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bukti')) {
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada perubahan skema untuk dibalikkan (no-op).
    }
};
