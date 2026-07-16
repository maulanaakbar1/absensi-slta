@extends('layouts.app')
@section('title', 'Data Ekstrakurikuler')

@section('content')
{{-- Menambahkan variabel 'search' ke dalam x-data --}}
<div x-data="{ 
    openAdd: false, 
    openEdit: false, 
    search: '', 
    editData: {
        id: '',
        nama: '',
        nama_satuan: '',
        deskripsi: ''
    } 
}">
    
    {{-- Header: Disesuaikan agar sama dengan Data Pembina --}}
    <div class="flex flex-col gap-4 mb-8">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Daftar Ekstrakurikuler</h3>
            <p class="text-slate-500 text-sm">Kelola semua cabang kegiatan ekskul SMKN 1 Talaga.</p>
        </div>

        {{-- Tombol Tambah --}}
        <div class="flex justify-start md:justify-end">
            <button @click="openAdd = true" 
                class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition flex items-center gap-2 w-fit"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Ekskul
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
                    placeholder="Cari nama ekskul..." 
                    class="pl-10 pr-4 py-2.5 w-full rounded-xl border border-slate-200 focus:ring-0 focus:border-blue-500 outline-none transition text-sm bg-white shadow-sm">
        </div>
    </div>

    {{-- Grid Card dengan Filter AlpineJS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($ekskuls as $e)
        {{-- Logic Filter --}}
        <div x-show="'{{ strtolower($e->nama) }}'.includes(search.toLowerCase())"
             x-transition.opacity
             class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-md transition">
            
            <div class="flex items-start justify-between mb-4">
                <div class="h-14 w-14 rounded-2xl bg-blue-50 flex items-center justify-center overflow-hidden border border-blue-100">
                    @if($e->foto)
                        <img src="{{ asset('storage/'.$e->foto) }}" class="object-cover h-full w-full">
                    @else
                        <span class="text-blue-600 font-bold text-xl">{{ substr($e->nama, 0, 1) }}</span>
                    @endif
                </div>
                <div class="flex gap-1">
                    {{-- Tombol Edit --}}
                    <button 
                        @click="editData = {
                            id: '{{ $e->id }}',
                            nama: '{{ $e->nama }}',
                            nama_satuan: '{{ $e->nama_satuan }}',
                            deskripsi: '{{ $e->deskripsi }}'
                        }; openEdit = true"
                        class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor">
                            
                            <path stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        
                        </svg>
                    </button>
                    
                    {{-- Tombol Hapus dengan SweetAlert2 Konfirmasi --}}
                    <form action="{{ route('admin.ekskul.destroy', $e->id) }}" method="POST" class="form-delete">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                </div>
            </div>
            <h4 class="text-lg font-bold text-slate-800">{{ $e->nama }}</h4>
            @if($e->nama_satuan)
                <p class="text-xs font-bold uppercase tracking-wider text-blue-500 mt-1">
                    {{ $e->nama_satuan }}
                </p>
            @endif
            <p class="text-slate-500 text-sm mt-2 line-clamp-2 leading-relaxed">{{ $e->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
        </div>
        @empty
        <div class="col-span-full py-20 text-center opacity-40">
            <p class="text-xl font-medium">Belum ada data Ekstrakurikuler</p>
        </div>
        @endforelse
    </div>

    {{-- Modal Tambah --}}
    <div x-show="openAdd" 
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        <div @click.away="openAdd = false" class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl relative">
            <h4 class="text-xl font-bold mb-6 text-slate-800">Tambah Ekskul Baru</h4>
            <form action="{{ route('admin.ekskul.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Nama Ekskul</label>
                    <input type="text" name="nama" placeholder="Contoh: Paskibra" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition" required>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">
                        Nama Satuan
                    </label>

                    <input
                        type="text"
                        name="nama_satuan"
                        placeholder="Contoh: Ambalan, Rayon, Regu"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                    >
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi" placeholder="Jelaskan visi atau kegiatan ekskul..." class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition" rows="3"></textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Logo / Foto Kegiatan</label>
                    <input type="file" name="foto" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openAdd = false" class="flex-1 px-4 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" class="flex-[2] bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition">Simpan Ekskul</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="openEdit" 
            class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-cloak>
        <div @click.away="openEdit = false" class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl relative">
            <h4 class="text-xl font-bold mb-6 text-slate-800">Edit Data Ekskul</h4>
            <form :action="`{{ url('admin/ekskul') }}/${editData.id}`" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Nama Ekskul</label>
                    <input type="text" name="nama" x-model="editData.nama" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition" required>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">
                        Nama Satuan
                    </label>

                    <input
                        type="text"
                        name="nama_satuan"
                        x-model="editData.nama_satuan"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                    >
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi" x-model="editData.deskripsi" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition" rows="3"></textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Logo / Foto Kegiatan (Kosongkan jika tidak diubah)</label>
                    <input type="file" name="foto" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openEdit = false" class="flex-1 px-4 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" class="flex-[2] bg-amber-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-amber-200 hover:bg-amber-600 transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Script SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Alert Sukses untuk Create & Update (Membaca session flash Laravel)
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000,
            customClass: {
                popup: 'rounded-[2rem]'
            }
        });
    @endif

    // 2. Konfirmasi Custom Sebelum Hapus Data
    function confirmDelete(button) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data ekskul yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b',  
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl font-bold px-4 py-2',
                cancelButton: 'rounded-xl font-bold px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form tujuan dari button yang di-klik
                button.closest('.form-delete').submit();
            }
        });
    }
</script>
@endsection