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
        Schema::create('detail_periksa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_periksa');
            $table->unsignedInteger('id_obat');

            $table->foreign('id_periksa')
                ->references('id')
                ->on('periksa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('id_obat')
                ->references('id')
                ->on('obat')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_periksa');
    }
};
