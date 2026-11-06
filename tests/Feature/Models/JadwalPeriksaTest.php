<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JadwalPeriksaTest extends TestCase
{
    public function test_doctor_can_open_the_schedule_list(): void
    {
        $this->createScheduleTable();

        $doctor = new User(['nama' => 'Dokter Test', 'role' => 'dokter']);
        $doctor->setAttribute($doctor->getKeyName(), 2);

        $response = $this->actingAs($doctor)->get(route('jadwal-periksa.index'));

        $response->assertOk();
    }

    public function test_doctor_can_create_a_schedule_without_timestamp_columns(): void
    {
        $this->createScheduleTable();

        $doctor = new User(['nama' => 'Dokter Test', 'role' => 'dokter']);
        $doctor->setAttribute($doctor->getKeyName(), 2);

        $response = $this->actingAs($doctor)->post(route('jadwal-periksa.store'), [
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '13:00',
        ]);

        $response->assertRedirectToRoute('jadwal-periksa.index');
        $this->assertDatabaseHas('jadwal_periksa', [
            'id_dokter' => 2,
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '13:00',
        ]);
    }

    private function createScheduleTable(): void
    {
        Schema::dropIfExists('jadwal_periksa');
        Schema::create('jadwal_periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_dokter');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
        });
    }
}
