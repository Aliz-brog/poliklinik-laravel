<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\Periksa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPasienController extends Controller
{
    public function index(): View
    {
        $riwayatPasien = Periksa::query()
            ->whereHas('daftarPoli.jadwalPeriksa', function (Builder $query): void {
                $query->where('id_dokter', Auth::id());
            })
            ->with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat',
            ])
            ->orderByDesc('tgl_periksa')
            ->get();

        $riwayatPasien->each(fn (Periksa $periksa) => $this->markPoliDescriptionComplaint($periksa->daftarPoli));

        return view('dokter.riwayat-pasien.index', compact('riwayatPasien'));
    }

    public function show(int $id): View
    {
        $periksa = Periksa::query()
            ->whereHas('daftarPoli.jadwalPeriksa', function (Builder $query): void {
                $query->where('id_dokter', Auth::id());
            })
            ->with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat',
            ])
            ->findOrFail($id);

        $this->markPoliDescriptionComplaint($periksa->daftarPoli);

        return view('dokter.riwayat-pasien.show', compact('periksa'));
    }

    private function markPoliDescriptionComplaint(?DaftarPoli $daftarPoli): void
    {
        $poli = $daftarPoli?->jadwalPeriksa?->dokter?->poli;
        $isPoliDescription = $daftarPoli !== null
            && filled($daftarPoli->keluhan)
            && $poli?->descriptionIsContainedIn($daftarPoli->keluhan) === true;

        $daftarPoli?->setAttribute('keluhan_adalah_keterangan_poli', $isPoliDescription);
    }
}
