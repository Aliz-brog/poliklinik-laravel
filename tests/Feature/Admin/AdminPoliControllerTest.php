<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPoliControllerTest extends TestCase
{
    public function test_admin_can_add_a_poli_without_database_timestamp_columns(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama');
            $table->string('role');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });
        Schema::create('poli', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_poli', 100);
            $table->text('keterangan')->nullable();
        });
        DB::table('users')->insert([
            'id' => 1,
            'nama' => 'Admin Test',
            'role' => 'admin',
        ]);
        $admin = User::findOrFail(1);

        $response = $this->actingAs($admin)
            ->post(route('polis.store'), [
                'nama_poli' => 'Poli Umum',
                'keterangan' => 'Pemeriksaan kesehatan umum',
            ]);

        $response->assertRedirectToRoute('polis.index');
        $response->assertSessionHas('success', 'Poli berhasil di tambahkan');
        $this->assertDatabaseHas('poli', [
            'nama_poli' => 'Poli Umum',
            'keterangan' => 'Pemeriksaan kesehatan umum',
        ]);
    }
}
