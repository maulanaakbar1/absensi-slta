@extends('layouts.app')

@section('title_page', 'Riwayat Absensi')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        
        {{-- HEADER PANEL --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Semua Riwayat Absensi</h3>
                <p class="text-xs text-slate-500">Daftar kehadiran Anda selama ini</p>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-16">No</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-24">Foto</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b whitespace-nowrap">Hari & Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b whitespace-nowrap">Jam Masuk</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-28">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b w-1/3">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-28">Lokasi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($semuaRiwayat as $index => $row)
                    <tr class="hover:bg-slate-50/40 transition group">
                        {{-- NO (CENTER) --}}
                        <td class="px-6 py-4 text-sm text-slate-400 text-center font-medium">
                            {{ $semuaRiwayat->firstItem() + $index }}
                        </td>
                        
                        {{-- FOTO (CENTER) --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center">
                                @if($row->foto)
                                    <button type="button" 
                                        onclick="openImage('{{ asset('storage/' . $row->foto) }}')"
                                        class="focus:outline-none block">
                                        <img src="{{ asset('storage/' . $row->foto) }}" 
                                            class="w-16 h-12 object-cover rounded-lg shadow-sm border border-slate-200 group-hover:scale-105 transition duration-200 cursor-zoom-in">
                                    </button>
                                @else
                                    <div class="w-16 h-12 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] text-slate-400 font-bold tracking-wider border border-dashed border-slate-200">
                                        KOSONG
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- HARI & TANGGAL (LEFT - FIXED ONE LINE) --}}
                        <td class="px-6 py-4 text-sm font-semibold text-slate-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('l, d F Y') }}
                        </td>

                        {{-- JAM MASUK (LEFT - FIXED ONE LINE) --}}
                        <td class="px-6 py-4 text-sm font-medium text-slate-600 whitespace-nowrap">
                            {{ $row->jam_masuk }} <span class="text-xs text-slate-400 font-normal">WIB</span>
                        </td>

                        {{-- STATUS (CENTER) --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColor = [
                                    'hadir' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'sakit' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'izin'  => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'alpa'  => 'bg-red-50 text-red-600 border-red-100',
                                ][$row->status] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                            @endphp
                            <span class="inline-block min-w-[70px] px-2.5 py-1 rounded-xl text-xs font-bold uppercase border {{ $statusColor }}">
                                {{ $row->status }}
                            </span>
                        </td>

                        {{-- KETERANGAN (LEFT) --}}
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($row->keterangan)
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-medium inline-block max-w-xs truncate" title="{{ $row->keterangan }}">
                                    {{ $row->keterangan }}
                                </span>
                            @else
                                <span class="text-slate-400 italic text-xs">Tidak ada keterangan</span>
                            @endif
                        </td>

                        {{-- LOKASI (CENTER) --}}
                        <td class="px-6 py-4 text-center">
                            @if($row->lokasi)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($row->lokasi) }}" target="_blank" 
                                    class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-white hover:bg-blue-600 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-full transition shadow-sm">
                                    <i class="fas fa-map-marker-alt text-[10px]"></i> Maps
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-red-500 font-medium bg-red-50 border border-red-100 px-2.5 py-1 rounded-full">
                                    <i class="fas fa-times-circle"></i> Gagal
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="fas fa-folder-open text-2xl text-slate-300"></i>
                                <p class="text-sm">Belum ada data absensi untuk ditampilkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        <div class="p-6 bg-slate-50 border-t border-slate-100">
            {{ $semuaRiwayat->links() }}
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO --}}
<div id="imageModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300 p-4">
    <div class="relative max-w-2xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-100 p-2">
        <button onclick="closeImage()" 
            class="absolute top-4 right-4 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition z-10 font-bold">
            ✕
        </button>
        <img id="modalImage" class="w-full max-h-[75vh] object-contain rounded-2xl">
    </div>
</div>
@endsection

@push('scripts')
<script>
function openImage(src) {
    document.getElementById('modalImage').src = src;
    const modal = document.getElementById('imageModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImage() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('click', function(e) {
    if (e.target.id === 'imageModal') {
        closeImage();
    }
});
</script>
@endpush