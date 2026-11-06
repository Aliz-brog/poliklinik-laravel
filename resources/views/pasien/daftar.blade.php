<x-layouts.app title="Daftar Poli">

    <div class="flex items-center justify-center px-4">
        <div class="w-full max-w-3xl">
            <div class="card bg-base-100 shadow">
                <div class="card-body">

                    <h2 class="text-2xl font-bold text-center mb-6">
                        🏥 Pendaftaran Poli
                    </h2>

                    {{-- Toast Success --}}
                    @if (session('message'))
                    <div id="toastSuccess" class="toast toast-top toast-end z-50">
                        <div class="alert alert-success shadow-lg">
                            <span> {{ session('message') }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Error --}}
                    @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                        @csrf

                        {{-- Nomor RM --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">
                                Nomor Rekam Medis
                            </label>

                            <input type="text" value="{{ $user->no_rm }}" readonly
                                class="w-full border-2 rounded-lg p-2 bg-gray-100">
                        </div>

                        {{-- Pilih Poli --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">
                                Pilih Poli
                            </label>

                            <select name="id_poli" id="poliSelect" class="w-full border-2 rounded-lg p-2" required>
                                <option value="">-- Pilih Poli --</option>
                                @foreach ($polis as $poli)
                                <option value="{{ $poli->id }}" @selected((string) old('id_poli') === (string) $poli->id)>
                                    {{ $poli->nama_poli }}
                                </option>
                                @endforeach
                            </select>

                        </div>

                        {{-- Pilih Jadwal --}}
                        <div class="mb-4">
                            <label class="font-semibold block mb-1">
                                Pilih Jadwal Periksa
                            </label>

                            <select name="id_jadwal" id="jadwalSelect" class="w-full border-2 rounded-lg p-2" required disabled>
                                <option value="">Pilih poli terlebih dahulu</option>

                                @foreach ($jadwals as $jadwal)
                                <option value="{{ $jadwal->id }}" data-poli="{{ $jadwal->dokter->id_poli }}">
                                    {{ $jadwal->hari }}
                                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                    Dr. {{ $jadwal->dokter->nama ?? '--' }}
                                </option>
                                @endforeach

                            </select>
                            <p id="jadwalMessage" class="mt-1 text-sm text-slate-500" aria-live="polite"></p>
                        </div>

                        {{-- Patient symptoms --}}
                        <div class="mb-6">
                            <label class="font-semibold block mb-1">
                                Keluhan Sakit / Gejala yang Dirasakan
                            </label>
                            <textarea name="keluhan" rows="3" class="w-full border-2 rounded-lg p-2"
                                placeholder="Contoh: demam sejak kemarin, batuk, atau nyeri kepala">{{ old('keluhan') }}</textarea>
                            <p class="mt-1 text-sm text-slate-500">
                                Tuliskan gejala yang dirasakan pasien, bukan keterangan poli.
                            </p>
                            @error('keluhan')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary min-w-36">
                                Daftar Poli
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            const poliSelect = document.getElementById("poliSelect")
            const jadwalSelect = document.getElementById("jadwalSelect")
            const jadwalMessage = document.getElementById("jadwalMessage")
            const placeholder = jadwalSelect.options[0].cloneNode(true)
            const jadwalOptions = Array.from(jadwalSelect.options).slice(1)

            function updateJadwal(poliId, selectedJadwalId = "") {
                const jadwalTersedia = jadwalOptions
                    .filter(option => option.dataset.poli === poliId)
                    .map(option => option.cloneNode(true))

                jadwalSelect.replaceChildren(placeholder.cloneNode(true), ...jadwalTersedia)
                jadwalSelect.disabled = !poliId || jadwalTersedia.length === 0
                jadwalSelect.value = selectedJadwalId

                if (!poliId) {
                    jadwalMessage.textContent = "Pilih poli terlebih dahulu untuk melihat jadwal."
                } else if (jadwalTersedia.length === 0) {
                    jadwalMessage.textContent = "Belum ada jadwal dokter untuk poli ini."
                } else {
                    jadwalMessage.textContent = ""
                }
            }

            poliSelect.addEventListener("change", function(){
                updateJadwal(this.value)
            })

            updateJadwal(poliSelect.value, @json((string) old('id_jadwal', '')))
        })
    </script>
    @endpush
</x-layouts.app>