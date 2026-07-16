@extends('layouts.app')
@section('title', 'Data Pembina')

@section('content')
<div x-data="{ 
    openAdd: false, 
    openEdit: false, 
    search: '',
    editData: { id: '', name: '', nip: '', no_telp: '', ekskul_id: '' } }">
    
    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-8">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Manajemen Pembina</h3>
            <p class="text-slate-500 text-sm">Kelola data pembina ekstrakurikuler sekolah.</p>
        </div>

        {{-- Container tombol: mobile ke kiri, md (desktop) ke kanan --}}
        <div class="flex justify-start md:justify-end">
            <button @click="openAdd = true" 
                class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition flex items-center gap-2 w-fit"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pembina
            </button>
        </div>
    </div>

    {{-- Filter Pencarian --}}
    <div class="mb-6 flex justify-start">
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                    x-model="search" 
                    placeholder="Cari nama pembina..." 
                    class="pl-10 pr-4 py-2.5 w-full rounded-xl border border-slate-200 focus:ring-0 focus:border-blue-500 outline-none transition text-sm bg-white shadow-sm">
        </div>
    </div>

    {{-- Bagian Tabel dengan Filter --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-slate-500">Info Pembina</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-slate-500">NIP</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-slate-500">Kontak</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-slate-500">Ekstrakurikuler</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider font-bold text-slate-500 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembinas as $p)
                    <tr x-show="'{{ strtolower($p->user->name) }}'.includes(search.toLowerCase())" 
                        class="hover:bg-slate-50/80 transition"
                        x-transition.opacity>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($p->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 line-clamp-1">{{ $p->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                            {{ $p->nip ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                {{ $p->no_telp ?? 'No Contact' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($p->ekstrakurikuler)
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden">
                                        @if($p->ekstrakurikuler->foto)
                                            <img src="{{ asset('storage/' . $p->ekstrakurikuler->foto) }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">{{ $p->ekstrakurikuler->nama }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Tombol Edit --}}
                                <button @click="editData = { 
                                    id: '{{ $p->id }}',
                                    name: @js($p->user->name),
                                    email: @js($p->user->email),
                                    nip: @js($p->nip),
                                    no_telp: @js($p->no_telp),
                                    ekskul_id: @js($p->ekstrakurikuler_id)
                                }; openEdit = true"
                                        class="p-2 text-amber-500 hover:bg-amber-50 rounded-xl transition-colors"
                                        title="Edit Pembina">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                
                                {{-- Form Hapus terintegrasi SweetAlert2 --}}
                                <form action="{{ route('admin.pembina.destroy', $p->id) }}" method="POST" class="inline form-delete">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="button" 
                                        data-name="{{ $p->user->name }}"
                                        data-ekskul="{{ $p->ekstrakurikuler ? $p->ekstrakurikuler->nama : '—' }}"
                                        class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors btn-delete"
                                        title="Hapus Pembina">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="font-medium text-lg text-slate-500">Belum ada data pembina</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <div x-show="openAdd" 
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-cloak>
        <div @click.away="openAdd = false" class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl relative">
            <h4 class="text-xl font-bold text-slate-800 mb-6">Registrasi Pembina</h4>
            
            <form action="{{ route('admin.pembina.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">Informasi Akun</label>
                    <div class="space-y-3">
                        
                        <input type="text" name="name" placeholder="Nama Lengkap" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition" required>
                        <input type="email" name="email" placeholder="Email (untuk login)" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition" required>
                        <div x-data="{ password: '' }">

                            <input
                                type="password"
                                name="password"
                                x-model="password"
                                minlength="6"
                                placeholder="Password (Minimal 6 Karakter)"
                                required
                                :class="{
                                    'border-red-400': password.length > 0 && password.length < 6,
                                    'border-emerald-400': password.length >= 6
                                }"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                            >

                            <p
                                x-show="password.length > 0 && password.length < 6"
                                class="mt-1 text-xs text-red-500"
                            >
                                Password minimal 6 karakter
                            </p>

                            <p
                                x-show="password.length >= 6"
                                class="mt-1 text-xs text-emerald-500"
                            >
                                ✓ Password sudah valid
                            </p>

                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">Detail Tambahan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="nip" placeholder="NIP" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition">
                        <input type="text" name="no_telp" placeholder="No HP" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition">
                    </div>
                </div>

                <select name="ekstrakurikuler_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:border-blue-500 transition" required>
                    <option value="">Pilih Ekskul</option>
                    @foreach($ekskuls as $e)
                        <option value="{{ $e->id }}">{{ $e->nama }}</option>
                    @endforeach
                </select>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openAdd = false" class="flex-1 px-4 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" class="flex-[2] bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div x-show="openEdit"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak>

        <div
            @click.away="openEdit = false"
            class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl relative"
        >

            {{-- HEADER --}}
            <div class="mb-6">
                <h4 class="text-xl font-bold text-slate-800">
                    Perbarui Data Pembina
                </h4>

                <p class="text-slate-500 text-xs mt-1">
                    Pastikan data pembina sudah benar.
                </p>
            </div>

            <form :action="'{{ url('admin/pembina') }}/' + editData.id"
                method="POST"
                class="space-y-4">

                @csrf
                @method('PUT')

                {{-- INFORMASI AKUN --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">
                        Informasi Akun
                    </label>

                    <div class="space-y-3">
                        <input
                            type="text"
                            name="name"
                            x-model="editData.name"
                            placeholder="Nama Lengkap"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                            required
                        >

                        <input
                            type="email"
                            name="email"
                            x-model="editData.email"
                            placeholder="Email"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                            required
                        >

                        <div x-data="{ password: '' }">

                            <input
                                type="password"
                                name="password"
                                x-model="password"
                                minlength="6"
                                placeholder="Kosongkan jika tidak ingin mengubah password"
                                :class="{
                                    'border-red-400': password.length > 0 && password.length < 6,
                                    'border-emerald-400': password.length >= 6
                                }"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                            >

                            <p
                                x-show="password.length > 0 && password.length < 6"
                                class="mt-1 text-xs text-red-500"
                            >
                                Password minimal 6 karakter
                            </p>

                            <p
                                x-show="password.length >= 6"
                                class="mt-1 text-xs text-emerald-500"
                            >
                                ✓ Password baru sudah valid
                            </p>

                            <p
                                x-show="password.length == 0"
                                class="mt-1 text-xs text-slate-400"
                            >
                                Kosongkan jika tidak ingin mengubah password.
                            </p>

                            @error('password')
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
                    </div>
                </div>

                {{-- DETAIL --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">
                        Detail Tambahan
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <input
                            type="text"
                            name="nip"
                            x-model="editData.nip"
                            placeholder="NIP"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                        >

                        <input
                            type="text"
                            name="no_telp"
                            x-model="editData.no_telp"
                            placeholder="No HP"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                        >
                    </div>
                </div>

                {{-- EKSKUL --}}
                <select
                    name="ekstrakurikuler_id"
                    x-model="editData.ekskul_id"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:border-blue-500 transition"
                    required
                >
                    <option value="">Pilih Ekskul</option>

                    @foreach($ekskuls as $e)
                        <option value="{{ $e->id }}">
                            {{ $e->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- BUTTON --}}
                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        @click="openEdit = false"
                        class="flex-1 px-4 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="flex-[2] bg-amber-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-amber-200 hover:bg-amber-600 transition"
                    >
                        Update Data
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

</div>

{{-- SCRIPT SWEETALERT2 INTEGRATION --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Notifikasi Sukses Otomatis (Flash Session dari Controller)
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                html: `{!! nl2br(session('success')) !!}`,
                icon: 'success',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-[2rem]'
                }
            });
        @endif

        // 2. Konfirmasi Hapus Pembina (Intersept Klik Tombol)
        const deleteButtons = document.querySelectorAll('.btn-delete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.form-delete');
                const namaPembina = this.getAttribute('data-name');
                const ekskulPembina = this.getAttribute('data-ekskul');

                Swal.fire({
                    title: 'Hapus Data Pembina?',
                    html: `Apakah Anda yakin ingin menghapus <b>${namaPembina}</b>?<br><span class="text-xs text-slate-500 mt-2 block">Akses login akun pembina ini dan penugasan di ekskul <b>${ekskulPembina}</b> akan dicabut otomatis.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444', // Red-500
                    cancelButtonColor: '#64748b',  // Slate-500
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