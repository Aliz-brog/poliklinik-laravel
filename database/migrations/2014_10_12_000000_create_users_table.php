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
          
Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        }); $table->increments('id');
            $table->string('nama', 100);
            $table->string('alamat')->nullable();
            $table->string('no_ktp', 30)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('no_rm', 30)->nullable();
            $table->enum('role', ['admin', 'dokter', 'pasien']);
            $table->string('email', 100)->nullable()->unique();
            $table->string('password')->nullable();
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
