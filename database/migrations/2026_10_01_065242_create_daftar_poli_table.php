<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daftar_poli', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_pasien');
            $table->unsignedInteger('id_jadwal');
            $table->text('keluhan')->nullable();
            $table->integer('no_antrian')->nullable();

            $table->foreign('id_pasien')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('id_jadwal')
                ->references('id')
                ->on('jadwal_periksa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_poli');
    }
};
