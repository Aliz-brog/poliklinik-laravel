<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PoliController extends Controller
{
    public function get(): View
    {
        $user = Auth::user();

        if (blank($user->no_rm)) {
            $user->no_rm = now()->format('Ym').'-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT);
            $user->save();
        }

        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter:id,nama,id_poli')
            ->whereHas('dokter', function (Builder $query): void {
                $query->whereNotNull('id_poli');
            })
            ->get();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
        ]);

        $jadwalSesuaiPoli = JadwalPeriksa::query()
            ->whereKey($validated['id_jadwal'])
            ->whereHas('dokter', function (Builder $query) use ($validated): void {
                $query->where('id_poli', $validated['id_poli']);
            })
            ->exists();

        if (! $jadwalSesuaiPoli) {
            return back()
                ->withErrors(['id_jadwal' => 'Jadwal periksa tidak tersedia untuk poli yang dipilih.'])
                ->withInput();
        }

        $poli = Poli::findOrFail($validated['id_poli']);
        $keluhan = $validated['keluhan'] ?? null;

        if (filled($keluhan) && $poli->descriptionIsContainedIn($keluhan)) {
            return back()
                ->withErrors(['keluhan' => 'Tuliskan gejala yang dirasakan pasien, bukan keterangan poli.'])
                ->withInput();
        }

        $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $validated['id_jadwal'])->count();

        DaftarPoli::create([
            'id_pasien' => Auth::id(),
            'id_jadwal' => $validated['id_jadwal'],
            'keluhan' => $keluhan,
            'no_antrian' => $jumlahSudahDaftar + 1,
        ]);

        return redirect()
            ->back()
            ->with('message', 'Berhasil Mendaftar ke Poli')
            ->with('type', 'success');
    }
}
