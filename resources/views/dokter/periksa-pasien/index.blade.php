<x-layouts.app title="Periksa Pasien">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    <div class="card rounded-2 border bg-base-100 shadow-md">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarPasien as $dp)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $dp->pasien->nama }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    @if ($dp->keluhan_adalah_keterangan_poli)
                                        <span class="font-medium text-amber-700">Keluhan perlu dikonfirmasi</span>
                                    @else
                                        {{ $dp->keluhan ?: '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $dp->no_antrian }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($dp->periksas->isNotEmpty())
                                        <span class="badge rounded-lg border border-green-200 bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            <i class="fas fa-circle-check mr-1"></i>
                                            Sudah Diperiksa
                                        </span>
                                    @else
                                        <a href="{{ route('periksa-pasien.create', $dp->id) }}"
                                            class="btn btn-sm rounded-lg border-none bg-amber-500 px-4 text-white hover:bg-amber-600">
                                            <i class="fas fa-stethoscope"></i>
                                            Periksa
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-14 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <span>Tidak ada data pasien periksa</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>
