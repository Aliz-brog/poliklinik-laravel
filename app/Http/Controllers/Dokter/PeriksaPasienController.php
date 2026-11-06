<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeriksaPasienController extends Controller
{
    public function index(): View
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa.dokter.poli', 'periksas'])
            ->whereHas('jadwalPeriksa', function (Builder $query) use ($dokterId): void {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        $daftarPasien->each(function (DaftarPoli $daftarPoli): void {
            $daftarPoli->setAttribute(
                'keluhan_adalah_keterangan_poli',
                filled($daftarPoli->keluhan)
                    && $daftarPoli->jadwalPeriksa?->dokter?->poli?->descriptionIsContainedIn($daftarPoli->keluhan),
            );
        });

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create(int $id): View
    {
        $daftarPoli = $this->getUnexaminedRegistrationForDoctor($id);
        $obats = Obat::orderBy('nama_obat')->get();

        return view('dokter.periksa-pasien.create', compact('daftarPoli', 'obats'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $daftarPoli = $this->getUnexaminedRegistrationForDoctor($id);

        $validated = $request->validate([
            'obat_ids' => ['required', 'array', 'min:1'],
            'obat_ids.*' => ['integer', 'distinct', 'exists:obat,id'],
            'catatan' => ['nullable', 'string'],
        ], [
            'obat_ids.required' => 'Pilih minimal satu obat sebelum menyimpan pemeriksaan.',
            'obat_ids.array' => 'Pilih obat dari daftar yang tersedia.',
            'obat_ids.min' => 'Pilih minimal satu obat sebelum menyimpan pemeriksaan.',
            'obat_ids.*.integer' => 'Pilihan obat tidak valid.',
            'obat_ids.*.distinct' => 'Obat yang sama tidak dapat dipilih lebih dari satu kali.',
            'obat_ids.*.exists' => 'Obat yang dipilih tidak tersedia.',
        ]);

        DB::transaction(function () use ($validated, $daftarPoli): void {
            $totalHargaObat = Obat::whereKey($validated['obat_ids'])->sum('harga');

            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $validated['catatan'] ?? null,
                'biaya_periksa' => $totalHargaObat + 150000,
            ]);

            foreach ($validated['obat_ids'] as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);
            }
        });

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    private function getUnexaminedRegistrationForDoctor(int $id): DaftarPoli
    {
        $dokterId = Auth::id();

        return DaftarPoli::with(['pasien', 'jadwalPeriksa'])
            ->whereKey($id)
            ->whereHas('jadwalPeriksa', function (Builder $query) use ($dokterId): void {
                $query->where('id_dokter', $dokterId);
            })
            ->whereDoesntHave('periksas')
            ->firstOrFail();
    }
}
