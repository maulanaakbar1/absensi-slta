@extends('layouts.app')

@section('content')
    <div class="p-6">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Jadwal Latihan</h1>
                    <p class="text-slate-500 text-sm">Kelola rutinitas jadwal mingguan dan insidental ekstrakurikuler</p>
                </div>
            </div>

            <div class="flex justify-start md:justify-end">
                <button onclick="openModal('add')"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition flex items-center gap-2 w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jadwal
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER: Responsive Scroll --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-center w-28">Sifat / Waktu</th>
                            <th class="px-6 py-4">Waktu Latihan</th>
                            <th class="px-6 py-4">Lokasi & Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                            <tr class="hover:bg-slate-50/50 transition italic-none">
                                <td class="px-6 py-4 text-center">
                                    @if ($j->tipe === 'rutin' || empty($j->tanggal))
                                        <span
                                            class="text-xs font-black bg-blue-50 text-blue-600 px-2.5 py-1 rounded-md uppercase block mb-1">Rutin</span>
                                        <span class="text-sm font-bold text-slate-700 uppercase">{{ $j->hari }}</span>
                                    @else
                                        <span
                                            class="text-xs font-black bg-amber-50 text-amber-600 px-2.5 py-1 rounded-md uppercase block mb-1">Dadakan</span>
                                        <span
                                            class="text-xs font-bold text-slate-700">{{ date('d M Y', strtotime($j->tanggal)) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex flex-col">
                                        <span class="text-slate-700 font-bold text-sm">
                                            {{ date('H:i', strtotime($j->jam_mulai)) }} -
                                            {{ date('H:i', strtotime($j->jam_selesai)) }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Waktu
                                            Indonesia Barat</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800">{{ $j->lokasi }}</span>
                                        <span
                                            class="text-xs text-slate-500">{{ $j->keterangan ?? 'Tidak ada keterangan tambahan' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="editJadwal({{ json_encode($j) }})"
                                            class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition"
                                            title="Edit Jadwal">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('pembina.jadwal.destroy', $j->id) }}" method="POST"
                                            class="inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                data-info="{{ $j->tanggal ? date('d-m-Y', strtotime($j->tanggal)) : $j->hari }}"
                                                data-lokasi="{{ $j->lokasi }}"
                                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition btn-delete"
                                                title="Hapus Jadwal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <p class="italic text-sm text-slate-400">Belum ada jadwal latihan yang dibuat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL JADWAL --}}
    <div id="modalJadwal"
        class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        {{-- Ukuran dikecilkan menjadi max-w-sm dan padding menjadi p-6 --}}
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden transition-all duration-300">
            <div class="p-6">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-800 mb-4">Tambah Jadwal</h3>

                <form id="formJadwal" action="{{ route('pembina.jadwal.store') }}" method="POST">
                    @csrf
                    <div id="methodField"></div>

                    <div class="space-y-3.5">
                        {{-- Select Option Sifat Jadwal --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Sifat
                                Jadwal</label>
                            <select name="tipe" id="tipe" onchange="toggleTipeJadwal()"
                                class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm" required>
                                <option value="rutin">Rutin (Mingguan)</option>
                                <option value="dadakan">Dadakan (Insidental / Tanggal)</option>
                            </select>
                        </div>

                        {{-- Kondisional Input: Hari (Untuk Rutin) --}}
                        <div id="container-hari">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Hari
                                Latihan</label>
                            <select name="hari" id="hari"
                                class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm">
                                @foreach ($hariList as $hari)
                                    <option value="{{ $hari }}">{{ $hari }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kondisional Input: Tanggal (Untuk Dadakan) --}}
                        <div id="container-tanggal" class="hidden">
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal
                                Latihan</label>
                            <input type="date" name="tanggal" id="tanggal"
                                class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Mulai</label>
                                <input type="time" name="jam_mulai" id="jam_mulai"
                                    class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm" required>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Selesai</label>
                                <input type="time" name="jam_selesai" id="jam_selesai"
                                    class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm" required>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" placeholder="Contoh: Gedung Serbaguna"
                                class="w-full border-slate-200 rounded-xl focus:ring-blue-500 py-2.5 text-sm" required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="2" placeholder="Latihan Pasukan..."
                                class="w-full border-slate-200 rounded-xl focus:ring-blue-500 text-sm p-2.5"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-6">
                        <button type="button" onclick="closeModal()"
                            class="py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition text-xs">Batal</button>
                        <button type="submit"
                            class="py-2.5 bg-blue-600 text-white rounded-xl font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition text-xs text-center">Simpan
                            Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // Fungsi Manajemen Sembunyi/Tampil Input Field
        function toggleTipeJadwal() {
            const tipe = document.getElementById('tipe').value;
            const containerHari = document.getElementById('container-hari');
            const containerTanggal = document.getElementById('container-tanggal');
            const inputHari = document.getElementById('hari');
            const inputTanggal = document.getElementById('tanggal');

            if (tipe === 'rutin') {
                containerHari.classList.remove('hidden');
                containerTanggal.classList.add('hidden');

                inputHari.setAttribute('required', 'required');
                inputTanggal.removeAttribute('required');
                inputTanggal.value = ''; // Reset nilai tanggal
            } else {
                containerHari.classList.add('hidden');
                containerTanggal.classList.remove('hidden');

                inputTanggal.setAttribute('required', 'required');
                inputHari.removeAttribute('required');
            }
        }

        function openModal(mode) {
            const modal = document.getElementById('modalJadwal');
            const form = document.getElementById('formJadwal');
            const title = document.getElementById('modalTitle');
            const methodField = document.getElementById('methodField');

            modal.classList.remove('hidden');
            if (mode === 'add') {
                title.innerText = 'Tambah Jadwal Baru';
                form.action = "{{ route('pembina.jadwal.store') }}";
                methodField.innerHTML = '';
                form.reset();
                toggleTipeJadwal(); // Trigger initial state
            }
        }

        function editJadwal(data) {
            openModal('edit');
            const form = document.getElementById('formJadwal');
            const title = document.getElementById('modalTitle');
            const methodField = document.getElementById('methodField');

            title.innerText = 'Edit Jadwal Latihan';
            form.action = `/pembina/jadwal/${data.id}`;
            methodField.innerHTML = `@method('PUT')`;

            // Set value tipe berdasarkan data dari backend
            if (data.tanggal) {
                document.getElementById('tipe').value = 'dadakan';
                document.getElementById('tanggal').value = data.tanggal;
            } else {
                document.getElementById('tipe').value = 'rutin';
                document.getElementById('hari').value = data.hari;
            }

            toggleTipeJadwal(); // Sesuaikan visual form setelah nilai dimasukkan

            document.getElementById('jam_mulai').value = data.jam_mulai.substring(0, 5);
            document.getElementById('jam_selesai').value = data.jam_selesai.substring(0, 5);
            document.getElementById('lokasi').value = data.lokasi;
            document.getElementById('keterangan').value = data.keterangan || '';
        }

        function closeModal() {
            document.getElementById('modalJadwal').classList.add('hidden');
        }
    </script>

    {{-- Push SweetAlert Handlers --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                @if (session('success'))
                    Swal.fire({
                        title: 'Berhasil!',
                        text: "{{ session('success') }}",
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-[2rem]'
                        }
                    });
                @endif

                const deleteButtons = document.querySelectorAll('.btn-delete');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const form = this.closest('.form-delete');
                        const info = this.getAttribute('data-info');
                        const lokasi = this.getAttribute('data-lokasi');

                        Swal.fire({
                            title: 'Hapus Jadwal Latihan?',
                            text: `Jadwal latihan (${info}) di ${lokasi} akan dihapus dari sistem!`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal',
                            customClass: {
                                popup: 'rounded-[2.5rem]',
                                confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-sm',
                                cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-sm'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
