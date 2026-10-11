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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100);
            $table->string('alamat')->nullable();
            $table->string('no_ktp', 30)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('no_rm', 30)->nullable();
            $table->enum('role', ['admin', 'dokter', 'pasien']);
            $table->string('email', 100)->nullable()->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
