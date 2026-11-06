<?php

namespace Tests\Feature\Dokter;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RiwayatPasienControllerTest extends TestCase
{
    public function test_doctor_can_view_history_with_poli_description_flagged_as_complaint(): void
    {
        $this->createHistoryData();
        $doctor = User::findOrFail(1);

        $response = $this->actingAs($doctor)
            ->get(route('riwayat-pasien.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Pasien');
        $response->assertSee('Keluhan perlu dikonfirmasi');
        $response->assertDontSee('Poli umum adalah layanan pemeriksaan kesehatan dasar');
        $response->assertDontSee('Sakit perut');
    }

    public function test_doctor_can_still_see_a_valid_patient_complaint_in_history(): void
    {
        $this->createHistoryData();
        DB::table('daftar_poli')->where('id', 1)->update(['keluhan' => 'Demam']);
        $doctor = User::findOrFail(1);

        $response = $this->actingAs($doctor)
            ->get(route('riwayat-pasien.index'));

        $response->assertOk();
        $response->assertSee('Demam');
        $response->assertDontSee('Keluhan perlu dikonfirmasi');
    }

    public function test_doctor_can_view_history_detail_with_patient_and_prescribed_medicine(): void
    {
        $this->createHistoryData();
        $doctor = User::findOrFail(1);

        $response = $this->actingAs($doctor)
            ->get(route('riwayat-pasien.show', 1));

        $response->assertOk();
        $response->assertSee('Detail Riwayat');
        $response->assertSee('Pasien Test');
        $response->assertSee('Poli Umum');
        $response->assertSee('Dokter Test');
        $response->assertSee('Keluhan perlu dikonfirmasi');
        $response->assertDontSee('Poli umum adalah layanan pemeriksaan kesehatan dasar');
        $response->assertSee('Paracetamol');
        $response->assertSee('Rp 160.000');
    }

    public function test_doctor_cannot_view_another_doctors_history_detail(): void
    {
        $this->createHistoryData();
        $doctor = User::findOrFail(1);

        $response = $this->actingAs($doctor)
            ->get(route('riwayat-pasien.show', 2));

        $response->assertNotFound();
    }

    private function createHistoryData(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama');
            $table->string('role');
            $table->unsignedInteger('id_poli')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
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
        Schema::create('periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_daftar_poli');
            $table->dateTime('tgl_periksa');
            $table->text('catatan')->nullable();
            $table->integer('biaya_periksa')->nullable();
        });
        Schema::create('obat', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_obat');
            $table->string('kemasan')->nullable();
            $table->integer('harga');
        });
        Schema::create('detail_periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_periksa');
            $table->unsignedInteger('id_obat');
        });

        DB::table('poli')->insert([
            'id' => 1,
            'nama_poli' => 'Poli Umum',
            'keterangan' => 'layanan pemeriksaan kesehatan dasar yang menangani keluhan umum, melakukan diagnosis awal, tindakan medis sederhana, serta menerbitkan berbagai surat keterangan medis sesuai standar.',
        ]);
        DB::table('users')->insert([
            [
                'id' => 1,
                'nama' => 'Dokter Test',
                'role' => 'dokter',
                'id_poli' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'Pasien Test',
                'role' => 'pasien',
                'id_poli' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama' => 'Dokter Lain',
                'role' => 'dokter',
                'id_poli' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('jadwal_periksa')->insert([
            ['id' => 1, 'id_dokter' => 1, 'hari' => 'Senin', 'jam_mulai' => '09:00', 'jam_selesai' => '13:00'],
            ['id' => 2, 'id_dokter' => 3, 'hari' => 'Selasa', 'jam_mulai' => '09:00', 'jam_selesai' => '13:00'],
        ]);
        DB::table('daftar_poli')->insert([
            [
                'id' => 1,
                'id_pasien' => 2,
                'id_jadwal' => 1,
                'keluhan' => 'Poli umum adalah layanan pemeriksaan kesehatan dasar yang menangani keluhan umum, melakukan diagnosis awal, tindakan medis sederhana, serta menerbitkan berbagai surat keterangan medis sesuai standar',
                'no_antrian' => 1,
            ],
            [
                'id' => 2,
                'id_pasien' => 2,
                'id_jadwal' => 2,
                'keluhan' => 'Sakit perut',
                'no_antrian' => 2,
            ],
        ]);
        DB::table('periksa')->insert([
            [
                'id' => 1,
                'id_daftar_poli' => 1,
                'tgl_periksa' => '2026-10-30 10:00:00',
                'catatan' => 'Perlu istirahat.',
                'biaya_periksa' => 160000,
            ],
            [
                'id' => 2,
                'id_daftar_poli' => 2,
                'tgl_periksa' => '2026-10-30 11:00:00',
                'catatan' => 'Pemeriksaan rutin.',
                'biaya_periksa' => 160000,
            ],
        ]);
        DB::table('obat')->insert([
            'id' => 1,
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet',
            'harga' => 10000,
        ]);
        DB::table('detail_periksa')->insert([
            'id' => 1,
            'id_periksa' => 1,
            'id_obat' => 1,
        ]);
    }
}
