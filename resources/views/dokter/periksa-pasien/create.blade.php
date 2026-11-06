<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}"
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-slate-200">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Periksa Pasien</h2>
            <p class="text-sm text-slate-500">
                {{ $daftarPoli->pasien->nama }} · No. Antrian {{ $daftarPoli->no_antrian }}
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4 rounded-xl shadow-sm" role="alert">
            <span>Periksa kembali data yang diisi. Ada bagian yang belum valid.</span>
        </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            @php
                $oldObatIds = old('obat_ids', []);
                $selectedObatIds = array_map(
                    'strval',
                    is_array($oldObatIds) ? array_filter($oldObatIds, 'is_scalar') : []
                );
                $selectedObats = $obats->whereIn('id', $selectedObatIds);
            @endphp

            <form id="form-periksa" action="{{ route('periksa-pasien.store', $daftarPoli->id) }}" method="POST">
                @csrf

                {{-- Pilih Obat --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Obat <span class="text-red-500">*</span></span>
                    </label>
                    <select id="select-obat" class="select select-bordered w-full rounded-lg border-2 px-4">
                        <option value="">-- Pilih Obat --</option>
                        @foreach ($obats as $obat)
                            <option value="{{ $obat->id }}"
                                data-nama="{{ $obat->nama_obat }}"
                                data-harga="{{ $obat->harga }}"
                                @disabled($selectedObats->contains('id', $obat->id))>
                                {{ $obat->nama_obat }} - Rp{{ number_format($obat->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">
                        Pilih obat dari daftar. Harga obat akan tampil setelah obat dipilih.
                    </p>
                    @error('obat_ids')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('obat_ids.*')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Obat Terpilih --}}
                <div class="form-control mb-5">
                    <label class="label pb-1 ">
                        <span class="text-sm font-semibold text-gray-700">Obat Terpilih</span>
                    </label>

                    <ul id="obat-terpilih" class="mb-2 flex min-h-12 flex-col gap-2">
                        @forelse ($selectedObats as $obat)
                            <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700">
                                <span>{{ $obat->nama_obat }} — <span class="font-semibold">Rp {{ number_format($obat->harga, 0, ',', '.') }}</span></span>
                                <button type="button" data-hapus-obat="{{ $obat->id }}"
                                    class="btn btn-sm rounded-lg border-none bg-red-500 px-3 text-white hover:bg-red-600"
                                    aria-label="Hapus {{ $obat->nama_obat }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>
                        @empty
                            <li id="obat-kosong" class="py-2 text-sm text-slate-400">Belum ada obat yang dipilih.</li>
                        @endforelse
                    </ul>

                    <div id="obat-inputs">
                        @foreach ($selectedObats as $obat)
                            <input type="hidden" name="obat_ids[]" value="{{ $obat->id }}">
                        @endforeach
                    </div>
                </div>

                {{-- Total Harga --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Total Harga</span>
                    </label>
                    <div class="input input-bordered w-full rounded-lg flex items-center bg-slate-50 text-slate-700 font-bold" id="total-harga">
                        Rp 0
                    </div>
                    <p id="pesan-total-harga" class="mt-1 text-xs text-slate-500"
                        @if ($selectedObats->isNotEmpty()) hidden @endif>
                        Pilih minimal satu obat untuk melihat total harga obat.
                    </p>
                </div>

                {{-- Catatan --}}
                <div class="form-control mb-8">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Catatan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                    </label>
                    <textarea name="catatan" id="catatan" rows="4"
                        placeholder="Masukkan catatan..."
                        class="textarea textarea-bordered w-full border-2 px-4 py-2 rounded-lg resize-none">{{ old('catatan') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit"
                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>
                    <a href="{{ route('periksa-pasien.index') }}"
                        class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg px-6">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const selectObat = document.getElementById('select-obat');
        const listObat = document.getElementById('obat-terpilih');
        const obatInputs = document.getElementById('obat-inputs');
        const formPeriksa = document.getElementById('form-periksa');
        const totalHargaEl = document.getElementById('total-harga');
        const pesanTotalHarga = document.getElementById('pesan-total-harga');
        const selectedMedicines = new Map();
        const formatRupiah = new Intl.NumberFormat('id-ID');

        document.querySelectorAll('input[name="obat_ids[]"]').forEach((input) => {
            const option = Array.from(selectObat.options).find((item) => item.value === input.value);

            if (option) {
                selectedMedicines.set(input.value, {
                    id: input.value,
                    nama: option.dataset.nama,
                    harga: Number(option.dataset.harga),
                });
            }
        });

        function renderObat() {
            listObat.replaceChildren();
            let total = 0;

            selectedMedicines.forEach((obat) => {
                total += obat.harga;

                const item = document.createElement('li');
                item.className = 'flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700';

                const description = document.createElement('span');
                description.textContent = `${obat.nama} — Rp ${formatRupiah.format(obat.harga)}`;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm rounded-lg border-none bg-red-500 px-3 text-white hover:bg-red-600';
                removeButton.dataset.hapusObat = obat.id;
                removeButton.setAttribute('aria-label', `Hapus ${obat.nama}`);
                removeButton.innerHTML = '<i class="fas fa-trash"></i>';

                item.append(description, removeButton);
                listObat.appendChild(item);
            });

            selectObat.querySelectorAll('option').forEach((option) => {
                option.disabled = selectedMedicines.has(option.value);
            });
            obatInputs.replaceChildren();
            selectedMedicines.forEach((obat) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'obat_ids[]';
                input.value = obat.id;
                obatInputs.appendChild(input);
            });
            totalHargaEl.textContent = `Rp ${formatRupiah.format(total)}`;
            pesanTotalHarga.hidden = selectedMedicines.size > 0;

            if (selectedMedicines.size === 0) {
                const emptyMessage = document.createElement('li');
                emptyMessage.className = 'py-2 text-sm text-slate-400';
                emptyMessage.textContent = 'Belum ada obat yang dipilih.';
                listObat.appendChild(emptyMessage);
            }
        }

        selectObat.addEventListener('change', () => {
            const option = selectObat.options[selectObat.selectedIndex];

            if (option.value && !selectedMedicines.has(option.value)) {
                selectedMedicines.set(option.value, {
                    id: option.value,
                    nama: option.dataset.nama,
                    harga: Number(option.dataset.harga),
                });
            }

            selectObat.selectedIndex = 0;
            renderObat();
        });

        listObat.addEventListener('click', (event) => {
            const button = event.target.closest('[data-hapus-obat]');

            if (button) {
                selectedMedicines.delete(button.dataset.hapusObat);
                renderObat();
            }
        });

        formPeriksa.addEventListener('submit', (event) => {
            if (selectedMedicines.size === 0) {
                event.preventDefault();
                selectObat.setCustomValidity('Pilih minimal satu obat.');
                selectObat.reportValidity();
                return;
            }

            selectObat.setCustomValidity('');
        });

        selectObat.addEventListener('change', () => selectObat.setCustomValidity(''));

        renderObat();
    </script>

</x-layouts.app>