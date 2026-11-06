<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ObatTest extends TestCase
{
    public function test_admin_can_create_medicine_without_timestamp_columns(): void
    {
        Schema::dropIfExists('obat');
        Schema::create('obat', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_obat', 100);
            $table->string('kemasan', 100)->nullable();
            $table->integer('harga');
        });

        $admin = new User(['nama' => 'Admin Test', 'role' => 'admin']);
        $admin->setAttribute($admin->getKeyName(), 1);

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'tablet',
            'harga' => 13000,
        ]);

        $response->assertRedirectToRoute('obat.index');
        $this->assertDatabaseHas('obat', [
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'tablet',
            'harga' => 13000,
        ]);
    }
}
