<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->string('uploaded_by', 100)->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->dropColumn('uploaded_by');
        });
    }
};
