<?php

namespace Tests\Feature\Dokter;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PeriksaPasienControllerTest extends TestCase
{
    public function test_doctor_can_view_registered_patients_for_examination(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->get(route('periksa-pasien.index'));

        $response->assertOk();
        $response->assertViewIs('dokter.periksa-pasien.index');
        $response->assertSee('ID');
        $response->assertSee('Nama Pasien');
        $response->assertSee('Keluhan');
        $response->assertSee('No Antrian');
        $response->assertSee('Pasien Test');
        $response->assertSee('Periksa');
    }

    public function test_doctor_can_see_when_a_patient_has_already_been_examined(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();
        DB::table('periksa')->insert([
            'id' => 1,
            'id_daftar_poli' => 1,
            'tgl_periksa' => now(),
            'catatan' => 'Sudah diperiksa',
            'biaya_periksa' => 150000,
        ]);

        $response = $this->actingAs($doctor)
            ->get(route('periksa-pasien.index'));

        $response->assertOk();
        $response->assertSee('Sudah Diperiksa');
    }

    public function test_doctor_sees_a_poli_description_complaint_flagged_for_confirmation(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();
        $keteranganPoli = 'layanan pemeriksaan kesehatan dasar yang menangani keluhan umum, melakukan diagnosis awal, tindakan medis sederhana, serta menerbitkan berbagai surat keterangan medis sesuai standar.';
        $keluhanPoli = 'Poli umum adalah '.rtrim($keteranganPoli, '.');

        DB::table('poli')->insert([
            'id' => 1,
            'nama_poli' => 'Poli Umum',
            'keterangan' => $keteranganPoli,
        ]);
        DB::table('users')->where('id', 1)->update(['id_poli' => 1]);
        DB::table('daftar_poli')->where('id', 1)->update([
            'keluhan' => $keluhanPoli,
        ]);

        $response = $this->actingAs($doctor)
            ->get(route('periksa-pasien.index'));

        $response->assertOk();
        $response->assertSee('Keluhan perlu dikonfirmasi');
        $response->assertDontSee($keluhanPoli);
    }

    public function test_doctor_can_open_the_patient_examination_form(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->get(route('periksa-pasien.create', 1));

        $response->assertOk();
        $response->assertViewIs('dokter.periksa-pasien.create');
        $response->assertSee('Periksa Pasien');
        $response->assertSee('Pasien Test');
        $response->assertSee('Paracetamol');
        $response->assertSee('data-harga="10000"', false);
        $response->assertSee('Paracetamol - Rp10.000');
        $response->assertSee('Harga obat akan tampil setelah obat dipilih.');
        $response->assertSee('action="'.route('periksa-pasien.store', 1).'"', false);
        $response->assertSee('Obat Terpilih');
        $response->assertSee('Total Harga');
        $response->assertSee('Pilih Obat');
        $response->assertSee('Catatan');
        $response->assertSee('Simpan');
        $response->assertSee('Batal');
    }

    public function test_doctor_cannot_open_another_doctors_patient_examination_form(): void
    {
        $this->createExaminationTables();
        $this->createExaminationData();
        $otherDoctor = User::findOrFail(4);

        $response = $this->actingAs($otherDoctor)
            ->get(route('periksa-pasien.create', 1));

        $response->assertNotFound();
    }

    public function test_doctor_can_save_an_examination_and_selected_medicine(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->post(route('periksa-pasien.store', 1), [
                'catatan' => 'Demam ringan',
                'obat_ids' => [1],
                'biaya_periksa' => 999999,
            ]);

        $response->assertRedirectToRoute('periksa-pasien.index');
        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => 1,
            'catatan' => 'Demam ringan',
            'biaya_periksa' => 160000,
        ]);
        $this->assertDatabaseHas('detail_periksa', [
            'id_periksa' => 1,
            'id_obat' => 1,
        ]);
    }

    public function test_doctor_cannot_save_an_examination_for_another_doctors_patient(): void
    {
        $this->createExaminationTables();
        $this->createExaminationData();
        $otherDoctor = User::findOrFail(4);

        $response = $this->actingAs($otherDoctor)
            ->post(route('periksa-pasien.store', 1), [
                'catatan' => 'Tidak boleh disimpan',
                'obat_ids' => [1],
            ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('periksa', 0);
    }

    public function test_invalid_medicine_is_rejected_without_saving_an_examination(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->from(route('periksa-pasien.create', 1))
            ->post(route('periksa-pasien.store', 1), [
                'catatan' => 'Demam ringan',
                'obat_ids' => [999],
            ]);

        $response->assertRedirectToRoute('periksa-pasien.create', 1);
        $response->assertSessionHasErrors('obat_ids.0');
        $this->assertDatabaseCount('periksa', 0);
    }

    public function test_non_array_medicine_input_returns_to_the_form_with_a_validation_error(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->from(route('periksa-pasien.create', 1))
            ->followingRedirects()
            ->post(route('periksa-pasien.store', 1), [
                'catatan' => 'Demam ringan',
                'obat_ids' => 'invalid',
            ]);

        $response->assertOk();
        $response->assertSee('Periksa Pasien');
        $response->assertSee('Periksa kembali data yang diisi. Ada bagian yang belum valid.');
        $this->assertDatabaseCount('periksa', 0);
    }

    public function test_doctor_must_select_at_least_one_medicine_before_saving_an_examination(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();

        $response = $this->actingAs($doctor)
            ->from(route('periksa-pasien.create', 1))
            ->followingRedirects()
            ->post(route('periksa-pasien.store', 1), [
                'catatan' => 'Demam ringan',
            ]);

        $response->assertOk();
        $response->assertSee('Pilih minimal satu obat sebelum menyimpan pemeriksaan.');
        $response->assertSee('Pilih minimal satu obat untuk melihat total harga obat.');
        $this->assertDatabaseCount('periksa', 0);
    }

    public function test_doctor_cannot_examine_the_same_registration_twice(): void
    {
        $this->createExaminationTables();
        $doctor = $this->createExaminationData();
        DB::table('periksa')->insert([
            'id' => 1,
            'id_daftar_poli' => 1,
            'tgl_periksa' => now(),
            'catatan' => 'Sudah diperiksa',
            'biaya_periksa' => 150000,
        ]);

        $response = $this->actingAs($doctor)
            ->get(route('periksa-pasien.create', 1));

        $response->assertNotFound();
    }

    private function createExaminationTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama');
            $table->string('role');
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
        Schema::create('obat', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_obat');
            $table->string('kemasan')->nullable();
            $table->integer('harga');
        });
        Schema::create('periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_daftar_poli');
            $table->dateTime('tgl_periksa');
            $table->text('catatan')->nullable();
            $table->integer('biaya_periksa')->nullable();
        });
        Schema::create('detail_periksa', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_periksa');
            $table->unsignedInteger('id_obat');
        });
    }

    private function createExaminationData(): User
    {
        DB::table('users')->insert([
            ['id' => 1, 'nama' => 'Dokter Test', 'role' => 'dokter'],
            ['id' => 2, 'nama' => 'Pasien Test', 'role' => 'pasien'],
            ['id' => 4, 'nama' => 'Dokter Lain', 'role' => 'dokter'],
        ]);
        DB::table('jadwal_periksa')->insert([
            ['id' => 1, 'id_dokter' => 1, 'hari' => 'Senin', 'jam_mulai' => '09:00', 'jam_selesai' => '13:00'],
            ['id' => 2, 'id_dokter' => 4, 'hari' => 'Selasa', 'jam_mulai' => '09:00', 'jam_selesai' => '13:00'],
        ]);
        DB::table('daftar_poli')->insert([
            'id' => 1,
            'id_pasien' => 2,
            'id_jadwal' => 1,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);
        DB::table('obat')->insert([
            'id' => 1,
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet',
            'harga' => 10000,
        ]);

        return User::findOrFail(1);
    }
}
