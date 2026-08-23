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
        Schema::create('progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal1')->nullable(); 
            $table->string('status1')->nullable();
            $table->date('tanggal2')->nullable(); 
            $table->string('status2')->nullable();
            $table->date('tanggal3')->nullable(); 
            $table->string('status3')->nullable();
            $table->date('tanggal4')->nullable(); 
            $table->string('status4')->nullable();
            $table->date('tanggal5')->nullable(); 
            $table->string('status5')->nullable();
            $table->date('tanggal6')->nullable(); 
            $table->string('status6')->nullable();
            $table->date('tanggal7')->nullable(); 
            $table->string('status7')->nullable();
            $table->date('tanggal8')->nullable(); 
            $table->string('status8')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progresses');
    }
};
