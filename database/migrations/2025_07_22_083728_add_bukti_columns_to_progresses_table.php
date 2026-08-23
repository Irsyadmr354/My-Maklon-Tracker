<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('progresses', function (Blueprint $table) {
            for ($i = 1; $i <= 8; $i++) {
                $table->string("bukti$i")->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('progresses', function (Blueprint $table) {
            for ($i = 1; $i <= 8; $i++) {
                $table->dropColumn("bukti$i");
            }
        });
    }

};
