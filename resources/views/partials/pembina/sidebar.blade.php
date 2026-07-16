@php
    $pembina = \App\Models\Pembina::with('ekstrakurikuler')
        ->where('user_id', Auth::id())
        ->first();

    $ekskul = $pembina?->ekstrakurikuler;

    $logo = $ekskul && $ekskul->foto
        ? asset('storage/' . $ekskul->foto)
        : asset('images/default-ekskul.png');

    $namaSatuan = $ekskul->nama
        ?? $ekskul->nama_satuan
        ?? 'Ekstrakurikuler';
@endphp

<aside 
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:flex">

    {{-- HEADER --}}
    <div class="p-6 flex items-center justify-between">

        {{-- LOGO + TITLE --}}
        <div class="flex items-center gap-3">

            <div class="h-12 w-12 rounded-xl overflow-hidden shadow-lg bg-white border border-slate-100">

                <img 
                    src="{{ $logo }}"
                    alt="{{ $namaSatuan }}"
                    class="h-full w-full object-cover">

            </div>

            <div class="leading-tight">

                <h1 class="text-lg font-extrabold text-slate-800">
                    {{ $namaSatuan }}
                </h1>

                <p class="text-xs text-slate-400 font-medium">
                    Pembina Panel
                </p>

            </div>

        </div>

        {{-- CLOSE --}}
        <button 
            @click="sidebarOpen = false"
            class="md:hidden text-slate-400 hover:text-red-500 transition">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />

            </svg>

        </button>

    </div>

    {{-- MENU --}}
    <nav class="flex-1 px-4 space-y-2"
        x-data="{ 
            openAbsensi: {{ Request::is('pembina/rekap*') || Request::is('pembina/absensi/manage*') || Request::is('pembina/riwayat-absensi*') ? 'true' : 'false' }},
            openJadwal: {{ Request::is('pembina/jadwal*') || Request::is('pembina/hari-libur*') ? 'true' : 'false' }}
        }">

        {{-- Dashboard --}}
        <a href="{{ route('pembina.dashboard') }}" 
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-200
            {{ Request::is('pembina/dashboard') 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-slate-500 hover:bg-slate-50' }}">

            <svg xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor">

                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />

            </svg>

            Dashboard
        </a>

        {{-- Data Anggota --}}
        <a href="{{ route('pembina.anggota.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-200
            {{ Request::is('pembina/anggota*') 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-slate-500 hover:bg-slate-50' }}">

            <svg xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor">

                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />

            </svg>

            Data Anggota
        </a>

        {{-- DROPDOWN ABSENSI --}}
        <div class="relative">

            <button @click="openAbsensi = !openAbsensi"
                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300
                {{ Request::is('pembina/rekap*') || Request::is('pembina/absensi/manage*') || Request::is('pembina/riwayat-absensi*')
                    ? 'text-blue-600 bg-blue-50/50'
                    : 'text-slate-500 hover:bg-slate-50' }}">

                <div class="flex items-center gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />

                    </svg>

                    <span>Absensi</span>

                </div>

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform duration-300"
                    :class="openAbsensi ? 'rotate-180' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7" />

                </svg>

            </button>

            <div x-show="openAbsensi"
                x-cloak
                x-transition
                class="mt-1 ml-4 pl-4 border-l-2 border-slate-100 space-y-1">

                <a href="{{ route('pembina.absensi.manage') }}"
                    class="block px-4 py-2 text-sm rounded-lg
                    {{ Request::is('pembina/absensi/manage*')
                        ? 'text-blue-600 font-bold bg-blue-50/50'
                        : 'text-slate-500 hover:text-blue-600 hover:bg-slate-50' }}">

                    Manajemen

                </a>

                <a href="{{ route('pembina.rekap.index') }}"
                    class="block px-4 py-2 text-sm rounded-lg
                    {{ Request::is('pembina/rekap*')
                        ? 'text-blue-600 font-bold bg-blue-50/50'
                        : 'text-slate-500 hover:text-blue-600 hover:bg-slate-50' }}">

                    Rekap Bulanan

                </a>

                <a href="{{ route('pembina.riwayat.index') }}"
                    class="block px-4 py-2 text-sm rounded-lg
                    {{ Request::is('pembina/riwayat-absensi*')
                        ? 'text-blue-600 font-bold bg-blue-50/50'
                        : 'text-slate-500 hover:text-blue-600 hover:bg-slate-50' }}">

                    Riwayat

                </a>

            </div>

        </div>

        {{-- DROPDOWN JADWAL --}}
        <div class="relative">

            <button @click="openJadwal = !openJadwal"
                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-300
                {{ Request::is('pembina/jadwal*') || Request::is('pembina/hari-libur*')
                    ? 'text-blue-600 bg-blue-50/50'
                    : 'text-slate-500 hover:bg-slate-50' }}">

                <div class="flex items-center gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />

                    </svg>

                    <span>Jadwal</span>

                </div>

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform duration-300"
                    :class="openJadwal ? 'rotate-180' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7" />

                </svg>

            </button>

            <div x-show="openJadwal"
                x-cloak
                x-transition
                class="mt-1 ml-4 pl-4 border-l-2 border-slate-100 space-y-1">

                <a href="{{ route('pembina.jadwal.index') }}"
                    class="block px-4 py-2 text-sm rounded-lg
                    {{ Request::is('pembina/jadwal*')
                        ? 'text-blue-600 font-bold bg-blue-50/50'
                        : 'text-slate-500 hover:text-blue-600 hover:bg-slate-50' }}">

                    Latihan

                </a>

                <a href="{{ route('pembina.libur.index') }}"
                    class="block px-4 py-2 text-sm rounded-lg
                    {{ Request::is('pembina/hari-libur*')
                        ? 'text-blue-600 font-bold bg-blue-50/50'
                        : 'text-slate-500 hover:text-blue-600 hover:bg-slate-50' }}">

                    Hari Libur

                </a>

            </div>

        </div>

        <a href="{{ route('pembina.jurnal.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all duration-200
            {{ Request::is('pembina/jurnal*')
                ? 'bg-blue-50 text-blue-600'
                : 'text-slate-500 hover:bg-slate-50' }}">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>

            </svg>

            Jurnal

        </a>

    </nav>

</aside>