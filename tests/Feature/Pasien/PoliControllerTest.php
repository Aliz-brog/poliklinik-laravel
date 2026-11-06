<?php

namespace Tests\Feature\Pasien;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PoliControllerTest extends TestCase
{
    public function test_patient_can_open_the_registration_page_with_available_schedules(): void
    {
        $this->travelTo('2026-10-30 12:00:00');

        $patient = $this->createRegistrationData();

        $response = $this->actingAs($patient)->get('/pasien/daftar');

        $response->assertOk();
        $response->assertSee('Pendaftaran Poli');
        $response->assertSee('Dr. Dokter Test');
        $response->assertSee('data-poli="1"', false);
        $response->assertSee('data-poli="2"', false);
        $response->assertDontSee('Dokter Tanpa Poli');
        $response->assertSee('202610-003');
        $response->assertSee('Daftar Poli');
        $response->assertSee('Keluhan Sakit / Gejala yang Dirasakan');
        $response->assertSee('Tuliskan gejala yang dirasakan pasien, bukan keterangan poli.');
        $this->assertDatabaseHas('users', [
            'id' => 3,
            'no_rm' => '202610-003',
        ]);
    }

    public function test_patient_can_submit_a_registration_for_their_own_account(): void
    {
        $patient = $this->createRegistrationData();

        $response = $this->actingAs($patient)
            ->from(route('pasien.daftar'))
            ->post(route('pasien.daftar.submit'), [
                'id_pasien' => 2,
                'id_poli' => 1,
                'id_jadwal' => 1,
                'keluhan' => 'Demam',
            ]);

        $response->assertRedirectToRoute('pasien.daftar');
        $this->assertDatabaseHas('daftar_poli', [
            'id_pasien' => 3,
            'id_jadwal' => 1,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);
    }

    public function test_patient_cannot_submit_poli_description_as_a_complaint(): void
    {
        $patient = $this->createRegistrationData();

        $response = $this->actingAs($patient)
            ->from(route('pasien.daftar'))
            ->post(route('pasien.daftar.submit'), [
                'id_poli' => 1,
                'id_jadwal' => 1,
                'keluhan' => 'Poli Umum menyediakan pemeriksaan kesehatan dasar.',
            ]);

        $response->assertRedirectToRoute('pasien.daftar');
        $response->assertSessionHasErrors([
            'keluhan' => 'Tuliskan gejala yang dirasakan pasien, bukan keterangan poli.',
        ]);
        $this->assertDatabaseCount('daftar_poli', 0);
    }

    public function test_patient_cannot_submit_a_schedule_from_a_different_poli(): void
    {
        $patient = $this->createRegistrationData();

        $response = $this->actingAs($patient)
            ->from(route('pasien.daftar'))
            ->post(route('pasien.daftar.submit'), [
                'id_poli' => 2,
                'id_jadwal' => 1,
                'keluhan' => 'Demam',
            ]);

        $response->assertRedirectToRoute('pasien.daftar');
        $response->assertSessionHasErrors([
            'id_jadwal' => 'Jadwal periksa tidak tersedia untuk poli yang dipilih.',
        ]);
        $this->assertDatabaseCount('daftar_poli', 0);
    }

    private function createRegistrationData(): User
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama');
            $table->string('role');
            $table->string('no_rm')->nullable();
            $table->unsignedInteger('id_poli')->nullable();
            $table->timestamps();
        });
        Schema::create('poli', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_poli');
            $table->text('keterangan')->nullable();
        });
        Schema::create('jadwal_periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_dokter');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
        });
        Schema::create('daftar_poli', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_pasien');
            $table->unsignedInteger('id_jadwal');
            $table->text('keluhan')->nullable();
            $table->integer('no_antrian')->nullable();
        });

        DB::table('poli')->insert([
            [
                'id' => 1,
                'nama_poli' => 'Poli Umum',
                'keterangan' => 'Poli Umum menyediakan pemeriksaan kesehatan dasar.',
            ],
            [
                'id' => 2,
                'nama_poli' => 'Poli Gigi',
                'keterangan' => 'Poli Gigi menyediakan pemeriksaan kesehatan gigi.',
            ],
        ]);
        DB::table('users')->insert([
            [
                'id' => 2,
                'nama' => 'Dokter Test',
                'role' => 'dokter',
                'id_poli' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nama' => 'Dokter Poli Gigi',
                'role' => 'dokter',
                'id_poli' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nama' => 'Dokter Tanpa Poli',
                'role' => 'dokter',
                'id_poli' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('jadwal_periksa')->insert([
            ['id' => 1, 'id_dokter' => 2, 'hari' => 'Senin', 'jam_mulai' => '09:00', 'jam_selesai' => '13:00'],
            ['id' => 2, 'id_dokter' => 4, 'hari' => 'Selasa', 'jam_mulai' => '10:00', 'jam_selesai' => '14:00'],
            ['id' => 3, 'id_dokter' => 5, 'hari' => 'Rabu', 'jam_mulai' => '11:00', 'jam_selesai' => '15:00'],
        ]);
        DB::table('users')->insert([
            'id' => 3,
            'nama' => 'Pasien Test',
            'role' => 'pasien',
            'no_rm' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail(3);
    }
}
