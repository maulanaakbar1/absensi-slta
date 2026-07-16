@extends('layouts.app')

@section('title', 'Data Jadwal Ekskul')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">Jadwal Ekstrakurikuler</h1>
        <p class="text-slate-500">Pilih ekskul untuk melihat jadwal latihan & libur</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        @foreach($ekskuls as $ekskul)

        <a href="{{ route('admin.jadwal.show', $ekskul->id) }}"
           class="bg-white border border-slate-100 rounded-2xl p-5 hover:shadow-md transition group">

            <div class="flex items-center gap-4">

                {{-- LOGO EKSKUL --}}
                <div class="h-14 w-14 rounded-xl overflow-hidden bg-slate-100 flex items-center justify-center border">

                    @if($ekskul->foto)
                        <img 
                            src="{{ asset('storage/' . $ekskul->foto) }}"
                            alt="{{ $ekskul->nama }}"
                            class="h-full w-full object-cover">
                    @else
                        <span class="text-slate-500 font-bold text-sm">
                            {{ strtoupper(substr($ekskul->nama, 0, 2)) }}
                        </span>
                    @endif

                </div>

                {{-- INFO --}}
                <div class="flex-1">

                    <h2 class="font-semibold text-slate-800 group-hover:text-blue-600 transition">
                        {{ $ekskul->nama }}
                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        Klik untuk lihat jadwal latihan & hari libur
                    </p>

                </div>

                {{-- ICON ARROW --}}
                <div class="text-slate-300 group-hover:text-blue-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>
                </div>

            </div>

        </a>

        @endforeach

    </div>

</div>

@endsection