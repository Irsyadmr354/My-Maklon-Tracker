<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->string('assigned_to', 50)->nullable()->after('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }
};
